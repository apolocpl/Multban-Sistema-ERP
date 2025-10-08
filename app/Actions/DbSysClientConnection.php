<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

readonly class DbSysClientConnection
{
    public function __construct(protected User $user) {}

    public function execute()
    {
        DB::purge('dbsysclient');
        $dateBase = $this->user->getUserDataBase();

        config()->set('database.connections.dbsysclient.host', Crypt::decryptString($dateBase->bc_emp_host));
        config()->set('database.connections.dbsysclient.port', Crypt::decryptString($dateBase->bc_emp_porta));
        config()->set('database.connections.dbsysclient.database', Crypt::decryptString($dateBase->bc_emp_nome));
        config()->set('database.connections.dbsysclient.username', Crypt::decryptString($dateBase->bc_emp_user));
        config()->set('database.connections.dbsysclient.password', Crypt::decryptString($dateBase->bc_emp_pass));

        DB::reconnect('dbsysclient');
    }
}
