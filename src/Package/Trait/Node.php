<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Exception;
trait Node {

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
        foreach($read as $nr => $file){
            if($file->type == File::TYPE){
                $file->owner = File::owner($file->url);
                $file->group = File::group($file->url);
                $file->chmod = File::rights($file->url);
                $file->extension = File::extension($file->url);
                $file->basename = File::basename($file->url);
                $file->size = File::size($file->url);
                $size += $file->size;
                $list[] = $file;
                $count++;
            }
        }
        d(File::size_format($size));
        ddd($count);
    }
}

