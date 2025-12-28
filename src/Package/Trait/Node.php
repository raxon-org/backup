<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\App;

use Raxon\Module\Cli;
use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Exception;
use Raxon\Module\Sort;

trait Node {

    public function node_restore(object $flags, object $options): void
    {
        Core::interactive();
        if(!property_exists($options, 'source')){
            $options->source = '/mnt/Disk2/Media/Backup/';
        }
        if(!property_exists($options, 'date')){
            $options->source .= date('Ymd') . '/';
        } else {
            $options->source .= trim($options->date, '/') . '/';
        }
        if(property_exists($options, 'target') && !str_ends_with($options->target, '/')){
            $options->target .= '/';
        }
        $dir = new Dir();
        $read = $dir->read($options->source, true);
        if($read === false){
            throw new Exception('Directory not found: ' . $options->source);
        }
        $list = [];
        foreach($read as $nr => $file) {
            if ($file->type == File::TYPE) {
                $file->extension = File::extension($file->url);
                $file->basename = File::basename($file->url, $file->extension);
                $is_entry = false;
                if (!empty($options->include) && in_array($file->basename, $options->include, true)) {
                    $is_entry = true;
                }
                if (!empty($options->exclude) && in_array($file->basename, $options->exclude, true)) {
                    $is_entry = false;
                }
                if (empty($options->include) && empty($options->exclude)) {
                    $is_entry = true;
                }
                if (
                    stristr($file->basename, 'Node-') !== false &&
                    $file->extension == 'backup' &&
                    $is_entry
                ) {
                    $explode = explode('Node-', $file->basename);
                    if(array_key_exists(1, $explode)){
                        $number = (int) $explode[1];
                        $list[$number] = $file;
                    }
                }
            }
        }
        if(!array_key_exists(0, $list)){
            throw new Exception('No backup files found in: ' . $options->source);
        }
        ksort($list, SORT_NATURAL);
        $header = false;
        $boundary = false;
        $is_collect = false;
        $is_data = false;
        $collection = [];
        foreach($list as $nr => $file){
            $read = File::read($file->url);
            $data = explode(PHP_EOL, $read);
            if(!$header){
                $header = Core::object($data[0], Core::OBJECT);
                $boundary = $header->boundary ?? null;
            }
            if($boundary){
                foreach($data as $line){
                    if($line === $boundary . '-1'){
                        $is_collect = true;
                        $collection = [];
                        continue;
                    }
                    if($line === $boundary . '-2'){
                        $file = Core::object(implode(PHP_EOL, $collection), Core::OBJECT);
                        $is_collect = true;
                        $is_data = true;
                        $collection = [];
                        continue;
                    }
                    if(
                        $line === $boundary . '-3' &&
                        $file
                    ){
                        $explode = explode('/' . $file->name, $file->url);
                        $file->dir = $explode[0] . '/';
                        if(property_exists($options, 'target')){
                            $file->dir = $options->target;
                        }
                        Dir::create($file->dir, Dir::CHMOD);
                        $target = $file->dir . $file->name;
                        $collection = implode(PHP_EOL, $collection);
                        ddd($collection);
                        $write = gzdecode($collection);
                        ddd($write);
                        File::write($target, $write);
                        File::chmod($target, $file->chmod);
                        File::chown($target, $file->owner, $file->group);
                        echo 'Restored: ' . $target . PHP_EOL;
                        $is_collect = false;
                        $is_data = false;
                        $collection = [];
                        $file = false;
                        continue;
                    }
                    if($is_collect){
                        $collection[] = $line;
                    }
                }
            }
        }
    }


    /**
     * @throws Exception
     */
    public function node_create(object $flags, object $options): void
    {
        Core::interactive();
        $object = $this->object();
        $directory = $object->config('project.volume.dir.node');

        $dir = new Dir();
        $read = $dir->read($directory, true);
        $read = Sort::list($read)->with(['url' => Sort::ASC]);
        $count = 0;
        $number = 0;
        $max = count($read);
        $size = 0;
        $write = [];
        $boundary = Core::uuid() . '-' . Core::uuid();
        $header = (object) [
            'boundary' => $boundary,
            'time' => time(),
            'include' => $options->include ?? [],
            'exclude' => $options->exclude ?? [],
        ];
        $dir_output = '/mnt/Disk2/Media/Backup/' . date('Ymd') . '/';
        Dir::create($dir_output, Dir::CHMOD);
        $url = $dir_output . 'Node-'. $number .'.backup';
        if(File::exist($url)){
            throw new Exception('Backup file already exists: ' . $url);
        }
        File::append($url, Core::object($header, Core::JSON_LINE) . PHP_EOL);
        echo 'Initializing backup...' . PHP_EOL .PHP_EOL;
        foreach($read as $nr => $file){
            if($file->type == File::TYPE){
                $file->owner = File::owner($file->url);
                $file->group = File::group($file->url);
                $file->chmod = File::rights($file->url);
                $file->extension = File::extension($file->url);
                $file->basename = File::basename($file->url, $file->extension);
                $is_entry = false;

                if(!empty($options->include) && in_array($file->basename, $options->include, true)){
                    $is_entry = true;
                }
                if(!empty($options->exclude) && in_array($file->basename, $options->exclude, true)){
                    $is_entry = false;
                }
                if(empty($options->include) && empty($options->exclude)){
                    $is_entry = true;
                }
                if($is_entry){
                    $file->size = File::size($file->url);
                    File::append($url, $boundary . '-1' . PHP_EOL);
                    File::append($url, Core::object($file, Core::JSON_LINE) . PHP_EOL);
                    File::append($url, $boundary . '-2' . PHP_EOL);
//                    $data = mb_str_split(gzencode(File::read($file->url), 9), 1024 * 5);
                    $data = mb_str_split(File::read($file->url), 1024 * 5);
                    $data_count = count($data);
                    foreach($data as $data_nr => $part){
                        File::append($url, $part);
                        if($data_count !== $data_nr + 1){
                            $number++;
                            $url = $dir_output . 'Node-'. $number . '.backup';
                        }
                    }
                    File::append($url, PHP_EOL . $boundary . '-3' . PHP_EOL);
                    if(File::size($url) > (1024 * 5)){
                        $number++;
                        $url = $dir_output . 'Node-'. $number . '.backup';
                    }
                    $count++;
                    if($max > 0){
                        $percentage = ($count / $max) * 100;
                        echo Cli::tput('cursor.up');
                        echo 'Percentage: ' . number_format($percentage, 2) . '%' . PHP_EOL;
                    }
                } else {
                    $max--;
                }

            } else {
                $max--;
            }
        }
    }
}

