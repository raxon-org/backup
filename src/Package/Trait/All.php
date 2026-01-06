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
}