<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

class UserChunkImportJob implements ShouldQueue
{
    use Queueable;

    protected array $chunk;

    public int $tries = 3;

    public int $timeout = 360;

    public function __construct(array $chunk)
    {
        $this->chunk = $chunk;
    }

    public function handle(): void
    {
        try {
            if (empty($this->chunk)) {
                return;
            }

            $guard = config('auth.defaults.guard', 'web');
            $now = now();

            // 1) Figure out which IDs already exist
            $ids = array_column($this->chunk, 'id');
            $existingIds = User::whereIn('id', $ids)->pluck('id')->all();
            $existingSet = array_flip($existingIds); // fast lookup

            // 2) Prepare user rows to insert, only for non-existing IDs
            $insertUsers = [];
            $userRoleById = []; // [user_id => ['title' => 'Admin', 'name' => 'admin']]

            foreach ($this->chunk as $row) {
                $uid = $row['id'] ?? null;
                if ($uid === null || isset($existingSet[$uid])) {
                    continue;
                }

                // normalize role
                $roleTitle = trim((string) ($row['role'] ?? 'Client'));
                if ($roleTitle === '') {
                    $roleTitle = 'Client';
                }
                $roleName = Str::slug($roleTitle, '-');

                $insertUsers[] = [
                    'id' => $uid,
                    'first_name' => $row['first_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'username' => $row['username'] ?? null,
                    'mobile' => $row['mobile'] ?? null,
                    'mobile_verified_at' => $this->parseDate($row['mobile_verified_at'] ?? null),
                    'is_whatsapp_user' => (bool) ($row['is_whatsapp_user'] ?? false),
                    'email' => $row['email'] ?? null,
                    'email_verified_at' => $this->parseDate($row['email_verified_at'] ?? null),
                    'image' => $row['image'] ?? null,
                    'last_login_at' => $this->parseDate($row['last_login_at'] ?? null),
                    'password' => $row['password'] ?? bcrypt('12345678'),
                    'status' => (($row['status'] ?? '') === 'Active') ? 1 : 0,
                    'created_at' => $this->parseDate($row['created_at'] ?? $now),
                    'updated_at' => $this->parseDate($row['updated_at'] ?? $now),
                ];

                $userRoleById[$uid] = ['title' => $roleTitle, 'name' => $roleName];
            }

            if (empty($insertUsers)) {
                return; // nothing new to insert
            }

            DB::transaction(function () use ($insertUsers, $userRoleById, $guard, $now) {
                // 3) Bulk insert users (ignoring duplicates just in case)
                User::withoutEvents(function () use ($insertUsers) {
                    // requires Laravel 8.32+ ; otherwise fallback to User::insert()
                    User::insertOrIgnore($insertUsers);
                });

                // 4) Ensure roles exist (bulk)
                $uniqueRoleNames = collect($userRoleById)->pluck('name')->unique()->values();

                // fetch existing roles for this guard
                $existingRoles = Role::query()
                    ->where('guard_name', $guard)
                    ->whereIn('name', $uniqueRoleNames)
                    ->get(['id', 'name'])
                    ->keyBy('name');

                // find missing roles
                $missing = [];
                foreach ($uniqueRoleNames as $rName) {
                    if (! isset($existingRoles[$rName])) {
                        // find any user with this role name to get title
                        $title = collect($userRoleById)
                            ->first(fn ($r) => $r['name'] === $rName)['title'] ?? Str::title(str_replace('-', ' ', $rName));

                        $missing[] = [
                            'name' => $rName,
                            'guard_name' => $guard,
                            // Optional "title" column if you added it to roles table
                            // comment out if your roles table doesn't have "title"
                            'title' => $title,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (! empty($missing)) {
                    // bulk create missing roles
                    Role::insert($missing);
                }

                // reload role map (after insert) -> [name => id]
                $rolesMap = Role::query()
                    ->where('guard_name', $guard)
                    ->whereIn('name', $uniqueRoleNames)
                    ->pluck('id', 'name'); // name => id

                // 5) Build pivot rows for model_has_roles and insert in bulk
                $modelType = User::class;
                $pivotRows = [];

                foreach ($userRoleById as $uid => $r) {
                    $roleId = $rolesMap[$r['name']] ?? null;
                    if (! $roleId) {
                        continue; // should not happen, but be safe
                    }
                    $pivotRows[] = [
                        'role_id' => $roleId,
                        'model_type' => $modelType,
                        'model_id' => $uid,
                    ];
                }

                // Insert in chunks to avoid packet size issues
                foreach (array_chunk($pivotRows, 1000) as $chunk) {
                    DB::table('model_has_roles')->insertOrIgnore($chunk);
                }
            });
        } catch (Throwable $e) {
            Log::error('User chunk import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function parseDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return null;
        }
    }
}
