<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\App;

use Raxon\Exception\DirectoryCreateException;
use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Module\Cli;
use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Exception;
use Raxon\Module\Sort;

trait All {

    const CATEGORIES = [
        'Application',
        'Audio',
        'Book',
        'Data',
        'Desktop',
        'Document',
        'Domain',
        'Log',
        'Node',
        'Package',
        'Photo',
        'Shared',
        'Video'
    ];

    const RESTART = [
        'raxon/basic apache2 restore',
        'raxon/basic apache2 restart',
        'raxon/basic php restore',
        'raxon/basic php restart',
        'raxon/basic cron restore',
        'raxon/basic cron restart',
    ];

    /**
     * @throws Exception
     */
    public function all_create(object $flags, object $options): void
    {
        Core::interactive();
        $object = $this->object();
        $exclude = $options->exclude ?? [];
        foreach(self::CATEGORIES as $item){
            if(in_array($item, $exclude, true)){
                continue;
            }
            $command = Core::binary($object) . ' raxon/backup ' . strtolower($item);
            Core::execute($object, $command, $output, $notification);
            if($output){
                echo $output . PHP_EOL;
            }
            if($notification){
                echo $notification . PHP_EOL;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function all_restore(object $flags, object $options): void
    {
        Core::interactive();
        $object = $this->object();
        $exclude = $options->exclude ?? [];
        $date = $options->date ?? null;
        foreach(self::CATEGORIES as $item){
            if(in_array($item, $exclude, true)){
                continue;
            }
            $command = Core::binary($object) . ' raxon/backup restore ' . strtolower($item);
            if($date){
                $command .= ' -date ' . $date;
            }
            Core::execute($object, $command, $output, $notification);
            if($output){
                echo $output . PHP_EOL;
            }
            if($notification){
                echo $notification . PHP_EOL;
            }
        }
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function all_restart(object $flags, object $options): void
    {
        $object = $this->object();
        Core::interactive();
        $commands = [];
        foreach(self::RESTART as $item){
            $commands[] = Core::binary($object) . ' ' . $item;
        }
        foreach ($commands as $command){
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