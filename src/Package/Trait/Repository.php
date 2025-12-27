<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\App;

use Raxon\Module\Core;
use Raxon\Module\Dir;
use Raxon\Module\File;

use Raxon\Node\Module\Node;

use Exception;
trait Repository {

    /**
     * @throws Exception
     */
    public function add(object $flags, object $options): void
    {
        Core::interactive();
        if(!property_exists($options, 'directory')){
            throw new Exception('Option directory not set...');
        }
        $object = $this->object();
        $class = 'System.Git';
        $node = new Node($object);
        $response = $node->record($class, $node->role_system(), [
            'filter' => [
                'directory' => $options->directory
            ]            
        ]);
        if(!$response){            
            $record = (object) [
                'directory' => $options->directory,                
            ];
            $response = $node->create($class, $node->role_system(), $record);
        }
        ddd($response);
    }
}

