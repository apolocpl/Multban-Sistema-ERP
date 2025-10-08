<?php

namespace App\Models\Multban\Traits;

trait DbSysClientTrait
{
    public function getConnectionName(): string
    {
        return 'dbsysclient';
    }
}
