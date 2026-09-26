<?php

namespace App\Jobs;

use App\Actions\Modules\Clients\SaltSyncClients;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SyncSaltSyncUsersJob implements ShouldQueue
{
    use Batchable, Queueable;

    public $tries = 3;

    public $timeout = 720;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $page,
        public int $perPage = 1000
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SaltSyncClients $client): void
    {
        $payload = $client->getAllClients($this->page, $this->perPage);

        $usersData = $payload['data'] ?? [];

        if (empty($usersData)) {
            return;
        }

        $now = now();
        $insertUsers = [];

        foreach ($usersData as $row) {

            $uid = $row['id'];

            $insertUsers[] = [
                'id' => $uid,
                'first_name' => $row['first_name'] ?? null,
                'last_name' => $row['last_name'] ?? null,
                'unique_id' => $row['cid'] ?? $row['employee_id'] ?? 'user-'.$uid,
                'mobile' => $row['mobile'] ?? null,
                'mobile_verified_at' => $this->parseDate($row['mobile_verified_at'] ?? null),
                'email' => $row['email'] ?? null,
                'email_verified_at' => $this->parseDate($row['email_verified_at'] ?? null),
                'password' => $row['password'] ?? bcrypt('12345678'),
                'status' => (($row['status'] ?? '') === 'Active') ? 1 : 0,
                'created_at' => $this->parseDate($row['created_at'] ?? $now),
                'updated_at' => $this->parseDate($row['updated_at'] ?? $now),
            ];
        }

        // Bulk upsert for performance
        DB::table('users')->upsert(
            $insertUsers,
            ['id'],
            [
                'first_name',
                'last_name',
                'mobile',
                'mobile_verified_at',
                'email',
                'email_verified_at',
                'password',
                'status',
                'created_at',
                'updated_at',
            ]
        );

        // Assign roles (idempotent)
        foreach ($usersData as $row) {
            if (! empty($row['role'])) {
                $user = User::find($row['id']);
                $role = Role::firstOrCreate([
                    'name' => Str::slug($row['role']),
                    'title' => $row['role'],
                    'guard_name' => 'api',
                ]);
                if ($user && $role) {
                    $user->syncRoles([$role]);
                }
            }
        }
    }

    private function parseDate($value)
    {
        return $value ? Carbon::parse($value) : null;
    }
}
