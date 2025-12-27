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
    public function main(object $flags, object $options): void
    {
        Core::interactive();
        $object = $this->object();
    }
}

