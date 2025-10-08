<?php

namespace App\Console\Commands;

use App\Actions\DbSysClientConnection;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;

class DbSysClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbsysclient {instruction} {--dbsysclient=}';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! User::find($this->option('dbsysclient'))) {
            $this->info("User: #{$this->option('dbsysclient')} Not Found.");
        }
        $db_sys_client = $this->option('dbsysclient');
        foreach (
            User::query()->when($db_sys_client, function (Builder $query) use ($db_sys_client) {

                return $query->where('user_id', '=', $db_sys_client);
            })->cursor()
                ->all() as $user
        ) {
            $this->info("Looping to user: #{$user->user_id}");
            app(DbSysClientConnection::class, [
                'user' => $user,
            ])->execute();

            Artisan::call($this->argument('instruction'), [], $this->output);
        }

        return self::SUCCESS;
    }
}
