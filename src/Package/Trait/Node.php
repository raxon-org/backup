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
        foreach($read as $nr => $file){
            $file->owner = File::owner($file->url);
            $file->permission = File::rights($file->url);
            ddd($file);
        }
    }
}

