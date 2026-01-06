<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Raxon\Node\Module\Node;

use Exception;
trait Setup {

    /**
     * @throws Exception
     */
    public function install(): void
    {
        Core::interactive();
        $object = $this->object();
        /**
         * scan dir if node System.Git has count: 0.
         */
    }

    public function setup_cron(): void
    {
        $object = $this->object();
        $dir_read = $object->config('project.dir.data') . 'Cron';
        $dir = new Dir();
        $list = $dir->read($dir_read);
        if(is_array($list)){
            foreach($list as $file){
                $read = explode("\n", File::read($file->url));
                $is_found = false;
                foreach($read as $line){
                    if(stripos($line, 'raxon/backup all') !== false){
                        $is_found = true;
                        break;
                    }
                }
                if($is_found === false){
                    $read[] = '0 0 0 * 5 root /usr/bin/app raxon/backup all -exclude[]=Audio -exclude[]=Video -exclude[]=Book';
                    File::write($file->url, implode("\n", $read));
                }
            }
            $command = Core::binary($object) . ' raxon/basic cron restore';
            Core::execute($object, $command, $output, $notification);
            if($output){
                echo $output . PHP_EOL;
            }
            if($notification){
                echo $notification . PHP_EOL;
            }
        }
    }
}

