<?php

namespace App\Support;

use Aws\CommandInterface;

class R2AclCompatibility
{
    public static function removeAcl(CommandInterface $command): void
    {
        unset($command['ACL']);
    }
}
