<?php
namespace Package\Raxon\Backup\Trait;

use Raxon\Module\Cli;
use Raxon\Module\Core;
use Raxon\Module\File;

use Raxon\Node\Module\Node;

use Exception;
trait Main {

    /**
     * @throws Exception
     */
    public function sync(object $flags, object $options): void
    {
        Core::interactive();
        $object = $this->object();
        $node = new Node($object);
        $class = 'System.Git';
        $role = $node->role_system();
        $response = $node->list(
            $class,
            $role,
            [
                'limit' => 100000                
            ]
        );
        if(!property_exists($options, 'message')){
            $options->message = 'update ' . date('Y-m-d H:i:s');
        }
        if(!property_exists($options, 'without-key')){
            $options->without_key = false;
        }
        if($options->without_key === false){
            $source = '/mnt/Vps3/Mount/Data/Ssh/id_ed25519';
            $target = '/root/.ssh/id_ed25519';
            File::copy($source, $target);
            File::chmod($target, 0600);
            $source = '/mnt/Vps3/Mount/Data/Ssh/id_ed25519.pub';
            $target = '/root/.ssh/id_ed25519.pub';
            File::copy($source, $target);
        }
        if($response && array_key_exists('list', $response)){
            foreach($response['list'] as $repository){
                $command = 'cd ' . $repository->directory . ' && git pull';
                echo Cli::info('Command', ['uppercase' => true]) . ' ' . $command . PHP_EOL;
                Core::execute($object, $command, $output, $notification);                
                if($output){
                    echo $output . PHP_EOL;
                }
                if($notification){
                    $explode = explode('safe.directory', $notification, 2);
                    if(array_key_exists(1, $explode)){
                        $command_safe = 'git config --global --add safe.directory' . $explode[1];
                        Core::execute($object, $command_safe, $output, $notification);
                        if($output){
                            echo $output . PHP_EOL;
                        }
                        if($notification){
                            echo $notification . PHP_EOL;
                        }                        
                        echo Cli::info('Command', ['uppercase' => true]) . ' ' . $command . PHP_EOL;
                        Core::execute($object, $command, $output, $notification);                
                        if($output){
                            echo $output . PHP_EOL;
                        }
                        if($notification){
                            echo $notification . PHP_EOL;
                        }
                    }        
                }
                $command = 'cd ' . $repository->directory . ' && git add .';
                echo Cli::info('Command', ['uppercase' => true]) . ' ' . $command . PHP_EOL;
                Core::execute($object, $command, $output, $notification);
                if($output){
                    echo $output . PHP_EOL;
                }
                if($notification){
                    echo $notification . PHP_EOL;
                }
                $command = 'cd ' . $repository->directory . ' && git commit -a -m "' . $options->message . '"';
                echo Cli::info('Command', ['uppercase' => true]) . ' ' . $command . PHP_EOL;
                Core::execute($object, $command, $output, $notification);
                if($output){
                    echo $output . PHP_EOL;
                }
                if($notification){
                    echo $notification . PHP_EOL;
                }
                $command = 'cd ' . $repository->directory . ' && git push';
                echo Cli::info('Command', ['uppercase' => true]) . ' ' . $command . PHP_EOL;
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
}

