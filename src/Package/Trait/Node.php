<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Exception;
trait Node {

    public function node_restore(object $flags, object $options): void
    {
        Core::interactive();
        $dir_output = '/mnt/Disk2/Media/Backup/';
        if(!property_exists($options, 'date')){
            $dir_output .= date('Ymd') . '/';
        } else {
            $dir_output .= trim($options->date, '/') . '/';
        }
        $dir = new Dir();
        $read = $dir->read($dir_output, true);
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
                if (stristr($file->basename, 'Node-') !== false && $file->extension == 'backup' && $is_entry) {
                    $explode = explode('Node-', $file->basename);
                    if(array_key_exists(1, $explode)){
                        $number = (int) $explode[1];
                        $list[$number] = $file;
                    }
                }
            }
        }
        ddd($list);
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
        $list = [];
        $count = 0;
        $size = 0;
        $write = [];
        $boundary = Core::uuid() . '-' . Core::uuid();
        $header = (object) [
            'boundary' => $boundary,
            'time' => time(),
            'include' => $options->include ?? [],
            'exclude' => $options->exclude ?? [],
        ];
        $write[] = Core::object($header, Core::JSON);
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
                $file->size = File::size($file->url);
                $write[] = $boundary . '-1';
                $write[] = Core::object($file, Core::JSON);
                $write[] = $boundary . '-2';
                $write[] = File::read($file->url);
                $write[] = $boundary . '-3';
                $size += $file->size;
                $count++;
            }
        }
        $dir_output = '/mnt/Disk2/Media/Backup/' . date('Ymd') . '/';
        Dir::create($dir_output, Dir::CHMOD);
        $write = mb_str_split(gzencode(implode("\n", $write), 9), 1024 * 1024 * 25); //split data in 25 MB chunks
        $chunk_count = count($write);
        for($i = 0; $i < $chunk_count; $i++){
            File::write($dir_output . 'Node-'. $i .'.backup', $write[$i]);
        }
        echo 'Written: ( ' . $count . ' files) ' . File::size_format($size) . PHP_EOL;
    }
}

