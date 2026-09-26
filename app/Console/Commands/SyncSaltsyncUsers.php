<?php

namespace App\Console\Commands;

use App\Actions\Modules\Clients\SaltSyncClients;
use App\Jobs\SyncSaltSyncUsersJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SyncSaltsyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-saltsync-users  {perPage=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all users from SaltSync service';

    /**
     * Execute the console command.
     */
    public function handle(SaltSyncClients $client)
    {
        $perPage = (int) $this->argument('perPage');

        $first = $client->getAllClients(1, $perPage);
        $lastPage = (int) ($first['meta']['last_page'] ?? 1);

        $jobs = [];
        for ($p = 1; $p <= $lastPage; $p++) {
            $jobs[] = new SyncSaltSyncUsersJob($p, $perPage);
        }

        Bus::batch($jobs)
            ->name('saltsync-users-sync')
            ->allowFailures()
            ->onQueue('default')
            ->dispatch();

        $this->info("Queued {$lastPage} jobs (perPage={$perPage}).");
    }
}
