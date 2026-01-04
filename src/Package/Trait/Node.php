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
    const CHUNK_SIZE = 1024 * 1024 * 5; // 5 MB
    const BACKUP_DIRECTORY_AFFIX = 'Node/';
    const BACKUP_FILE_PREFIX = 'Node-';

    public function node_restore(object $flags, object $options): void
    {
        $object = $this->object();
        Core::interactive();
        if(!property_exists($options, 'source')){
            $dir_backup = $object->config('project.dir.backup');
                $options->source = $dir_backup . self::BACKUP_DIRECTORY_AFFIX;
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
                    stristr($file->basename, self::BACKUP_FILE_PREFIX) !== false &&
                    $file->extension == 'backup' &&
                    $is_entry
                ) {
                    $explode = explode(self::BACKUP_FILE_PREFIX, $file->basename);
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
        $file_info = false;
        $is_collect = false;
        $is_extended = false;
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
                        $file_info = Core::object(implode(PHP_EOL, $collection), Core::OBJECT);
                        $is_collect = true;
                        $collection = [];
                        continue;
                    }
                    if(
                        $line === $boundary . '-3' &&
                        $file_info
                    ){
                        $explode = explode('/' . $file_info->name, $file_info->url);
                        $file_info->dir = $explode[0] . '/';
                        if(property_exists($options, 'target')){
                            $file_info->dir = str_replace($header->directory, $options->target, $file_info->dir);
                        }
                        Dir::create($file_info->dir, Dir::CHMOD);
                        $target = $file_info->dir . $file_info->name;
                        $collection = implode(PHP_EOL, $collection);
                        $write = gzdecode($collection);
                        if(!property_exists($file_info, 'chmod')){
                            ddd($file_info);
                        }
                        $file_info->chmod = octdec($file_info->chmod);
                        File::write($target, $write);
                        File::chmod($target, $file_info->chmod);
                        File::chown($target, $file_info->owner, $file_info->group);
                        echo 'Restored: ' . $target . PHP_EOL;
                        $is_collect = false;
                        $file_info = false;
                        $collection = [];
                        continue;
                    }
                    if($is_extended){
                        $collection_last = array_pop($collection);
                        $collection_last .= $line;
                        $collection[] = $collection_last;
                        $is_extended = false;
                        continue;
                    }
                    if($is_collect){
                        $collection[] = $line;
                    }
                }
                $is_extended = true;
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
        $directory = $object->config('project.dir.node');
        $dir = new Dir();
        if(property_exists($options, 'exclude')){
            $dir->ignore($options->exclude);
        }
        $read = $dir->read($directory, true);
        if($read === false){
            throw new Exception('Directory not found: ' . $directory);
        }
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
            'directory' => $directory,
        ];
        $dir_backup = $object->config('project.dir.backup');
        $dir_backup_data = $dir_backup . self::BACKUP_DIRECTORY_AFFIX;
        $dir_output = $dir_backup_data . date('Ymd') . '/';
        Dir::create($dir_output, Dir::CHMOD);
        File::permission($object, [
            'backup' => $dir_backup,
            'data' => $dir_backup_data,
            'output' => $dir_output,
        ]);
        $url = $dir_output . self::BACKUP_FILE_PREFIX . $number .'.backup';
        if(File::exist($url)){
            throw new Exception('Backup file already exists: ' . $url);
        }
        File::append($url, Core::object($header, Core::JSON_LINE) . PHP_EOL);
        File::permission($object, [
            'url' => $url,
        ]);
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
                elseif(!empty($options->exclude)){
                    $is_entry = true;
                }
                if(empty($options->include) && empty($options->exclude)){
                    $is_entry = true;
                }
                if($is_entry){
                    $file->size = File::size($file->url);
                    File::append($url, $boundary . '-1' . PHP_EOL);
                    File::append($url, Core::object($file, Core::JSON_LINE) . PHP_EOL);
                    File::append($url, $boundary . '-2' . PHP_EOL);
                    File::permission($object, [
                        'url' => $url,
                    ]);
                    $data = mb_str_split(gzencode(File::read($file->url), 9), self::CHUNK_SIZE);
                    $data_count = count($data);
                    foreach($data as $data_nr => $part){
                        File::append($url, $part);
                        File::permission($object, [
                            'url' => $url,
                        ]);
                        if($data_count !== $data_nr + 1){
                            $number++;
                            $url = $dir_output . self::BACKUP_FILE_PREFIX . $number . '.backup';
                        }
                    }
                    File::append($url, PHP_EOL . $boundary . '-3' . PHP_EOL);
                    File::permission($object, [
                        'url' => $url,
                    ]);
                    if(File::size($url) > (self::CHUNK_SIZE)){
                        $number++;
                        $url = $dir_output . self::BACKUP_FILE_PREFIX . $number . '.backup';
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

