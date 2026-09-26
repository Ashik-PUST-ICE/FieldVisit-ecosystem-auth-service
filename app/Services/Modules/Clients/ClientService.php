<?php

namespace App\Services\Modules\Clients;

use App\Models\Client;
use App\Models\Directory\UserNetworkIndex;
use App\Models\User;
use App\Services\Applications\Gateway\MachineTokenManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientService
{
    protected $model;

    public function __construct()
    {
        $this->model = new User;
    }

    public function index(array $filters): LengthAwarePaginator
    {
        $query = UserNetworkIndex::with('user');

        if (isset($filters['search'])) {
            $query->where('full_name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('mobile', 'like', '%' . $filters['search'] . '%')
                ->orWhere('cid', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function getClientType($query, string $type)
    {

        $query = UserNetworkIndex::with('user');
        $conditions = [
            'free_clients' => function ($q) {
                return $q->where(function ($q) {
                    $q->where('billing_amount', 0)
                        ->orWhereNull('billing_amount');
                });
            },
            'active_clients' => fn($q) => $q->where('network_status', 1),
            'deactive_clients' => fn($q) => $q->where('network_status', 0),
            'auto_off_clients' => fn($q) => $q->where('is_auto_suspend', true),

            'auto_on_clients' => fn($q) => $q->where('is_auto_suspend', true),

            'new_line' => fn($q) => $q->whereMonth('activation_date', now()->month)
                ->whereYear('activation_date', now()->year),

            'monthly_discount' => fn($q) => $q->whereNotNull('monthly_discount')
                ->where('monthly_discount', '>', 0),
            'birthday' => fn($q) => $q->whereMonth('dob', now()->month)
                ->whereDay('dob', now()->day),
        ];

        if ($type !== 'all' && isset($conditions[$type])) {
            $conditions[$type]($query);
        }

        return $query;
    }

    public function show(string $id, array $relations = [], bool $exception = true): ?User
    {
        $defaultRelations = ['client', 'addressBooks', 'userIdentities', 'phoneBooks'];
        $allRelations = array_merge($defaultRelations, $relations);
        $query = $this->model->with($allRelations);

        return $exception ? $query->findOrFail($id) : $query->find($id);
    }

    public function store(array $data): User
    {
        DB::beginTransaction();
        try {
            $authId = authId();

            // Create or fetch user
            $user = User::create([
                'first_name' => $data['basicInfo']['first_name'],
                'last_name' => $data['basicInfo']['last_name'] ?? null,
                'email' => $data['basicInfo']['email'] ?? null,
                'email_verified_at' => $data['basicInfo']['email_verified_at'] ?? null,
                'mobile' => $data['basicInfo']['mobile'],
                'mobile_verified_at' => $data['basicInfo']['mobile_verified_at'] ?? null,
                'whatsapp' => $data['basicInfo']['whatsapp'] ?? null,
                'whatsapp_verified_at' => $data['basicInfo']['whatsapp_verified_at'] ?? null,
                'password' => $data['basicInfo']['password'] ?? bcrypt($data['basicInfo']['mobile']),
                'image' => $data['basicInfo']['image']['url'] ?? null,
                'unique_id' => User::generateUniqueId(),
            ]);

            // role assign
            $user->assignRole('client');

            // Create or update client info
            $client = $user->client()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'dob' => $data['basicInfo']['dob'],
                    'user_group_id' => $data['basicInfo']['client_group_id'],
                    'note' => $data['reference']['client_note'] ?? null,
                ]
            );

            // Create address
            $address = $user->addressBooks()->create([
                'type' => 'home',
                'flat_no' => $data['address']['flat_no'],
                'house_no' => $data['address']['house_no'],
                'road_no' => $data['address']['road_no'],
                'address' => $data['address']['address'],
                'country_id' => $data['address']['country_id'],
                'state_id' => $data['address']['state_id'],
                'city_id' => $data['address']['city_id'],
                'branch_id' => $data['address']['branch_id'],
                'zone_id' => $data['address']['zone_id'],
                'subzone_id' => $data['address']['subzone_id'],
                'postal_code' => $data['address']['postal_code'],
                'latitude' => $data['address']['latitude'] ?? null,
                'longitude' => $data['address']['longitude'] ?? null,
                'created_by' => $authId,
            ]);

            // Create identity
            $identityData = $user->userIdentities()->create([
                'identity_type' => $data['identification']['identity_type'],
                'identity_number' => $data['identification']['identity_number'],
                'document_1' => $data['identification']['document_1']['url'],
                'document_2' => $data['identification']['document_2']['url'] ?? null,
                'verified_by' => $authId,
                'verified_at' => now(),
                'status' => 1,
            ]);

            // Create phone book entries from phones array
            if (isset($data['phones']) && is_array($data['phones'])) {
                foreach ($data['phones'] as $phoneData) {
                    $user->phoneBooks()->create([
                        'network_id' => $phoneData['network_id'] ?? null,
                        'type' => $phoneData['type'] ?? 'personal',
                        'country_code' => $phoneData['country_code'] ?? '+880',
                        'phone_number' => $phoneData['phone_number'] ?? $phoneData['phone_no'] ?? null,
                        'phone_verified_at' => $phoneData['phone_verified_at'] ?? null,
                        'description' => $phoneData['description'] ?? $phoneData['title'] ?? null,
                        'status' => $phoneData['status'] ?? 1,
                        'is_default' => $phoneData['is_default'] ?? false,
                        'verified_at' => $phoneData['verified_at'] ?? null,
                    ]);
                }
            }

            // Log activity
            log_activity($user->id, 'Created a new Client', $authId, 'client', 'created', [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'client_id' => $client->id,
                'address_id' => $address->id,
                'identity_id' => $identityData->id,
            ]);

            DB::commit();

            // Load relations
            $user->load(['client', 'addressBooks', 'userIdentities', 'phoneBooks']);

            return $user;
        } catch (\Throwable $e) {
            log_activity(null, 'Client creation failed', $authId, 'client', 'failed', ['error' => $e->getMessage()], 'error');
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser(string $id, array $attributes): User
    {
        try {
            $user = $this->model->findOrFail($id);

            $user->update([
                'first_name' => $attributes['first_name'] ?? $user->first_name,
                'last_name' => $attributes['last_name'] ?? $user->last_name,
                'email' => $attributes['email'] ?? $user->email,
                'email_verified_at' => $attributes['email_verified_at'] ?? $user->email_verified_at,
                'mobile' => $attributes['mobile'] ?? $user->mobile,
                'mobile_verified_at' => $attributes['mobile_verified_at'] ?? $user->mobile_verified_at,
                'whatsapp' => $attributes['whatsapp'] ?? $user->whatsapp,
                'whatsapp_verified_at' => $attributes['whatsapp_verified_at'] ?? $user->whatsapp_verified_at,
                'image' => $attributes['image'] ?? $user->image,
            ]);

            $client = $user->client;
            $user->client()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'dob' => $attributes['dob'] ?? $client?->dob,
                    'user_group_id' => $attributes['user_group_id'] ?? $client?->user_group_id,
                ]
            );

            log_activity($user->id, 'Updated a  User', authId(), 'client', 'updated', $user->getChanges() + [
                'id' => $user->id,
            ]);

            return $user;
        } catch (\Throwable $e) {
            log_activity($user->id, 'User update failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');

            throw $e;
        }
    }

    public function updateAddress(string $id, array $attributes): User
    {
        $user = $this->model->findOrFail($id);
        try {
            $addressBook = $user->addressBooks()->first();

            $addressBook->update([
                'type' => $attributes['type'] ?? $addressBook->type,
                'flat_no' => $attributes['flat_no'] ?? $addressBook->flat_no,
                'house_no' => $attributes['house_no'] ?? $addressBook->house_no,
                'road_no' => $attributes['road_no'] ?? $addressBook->road_no,
                'address' => $attributes['address'] ?? $addressBook->address,
                'country_id' => $attributes['country_id'] ?? $addressBook->country_id,
                'state_id' => $attributes['state_id'] ?? $addressBook->state_id,
                'city_id' => $attributes['city_id'] ?? $addressBook->city_id,
                'branch_id' => $attributes['branch_id'] ?? $addressBook->branch_id,
                'zone_id' => $attributes['zone_id'] ?? $addressBook->zone_id,
                'subzone_id' => $attributes['subzone_id'] ?? $addressBook->subzone_id,
                'postal_code' => $attributes['postal_code'] ?? $addressBook->postal_code,
                'latitude' => $attributes['latitude'] ?? $addressBook->latitude,
                'longitude' => $attributes['longitude'] ?? $addressBook->longitude,
            ]);

            log_activity($user->id, 'Updated client Address', authId(), 'client', 'updated', $addressBook->getChanges() + [
                'id' => $user->id,
            ]);

            return $user;
        } catch (\Throwable $e) {
            log_activity($user->id, 'Address update failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    public function updateIdentity(string $id, array $attributes): User
    {
        $user = $this->model->findOrFail($id);
        try {
            $authId = authId();
            $identity = $user->userIdentities()->first();
            if ($identity) {
                $identity->update([
                    'identity_type' => $attributes['identity_type'] ?? $identity->identity_type,
                    'identity_number' => $attributes['identity_number'] ?? $identity->identity_number,
                    'document_1' => $attributes['document_1'] ?? $identity->document_1,
                    'document_2' => $attributes['document_2'] ?? $identity->document_2,
                    'verified_by' => $authId,
                    'verified_at' => now(),
                    'status' => 1,
                ]);
            }

            log_activity($user->id, 'Updated client Identity', authId(), 'client', 'updated', $identity->getChanges() + [
                'id' => $user->id,
            ]);

            return $user;
        } catch (\Throwable $e) {
            log_activity($user->id, 'Identity update failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    public function updatePhoneBooks(string $id, array $attributes): User
    {
        DB::beginTransaction();
        $user = $this->model->findOrFail($id);
        try {

            $authId = authId();

            foreach ($attributes as $phoneData) {
                // Find by id if exists
                // $phoneBook = isset($phoneData['id']) ? $user->phoneBooks()->find($phoneData['id']) : $user->phoneBooks()->first();
                $phoneBook = isset($phoneData['id']) ? $user->phoneBooks()->find($phoneData['id']) : null;
                if ($phoneBook) {
                    $phoneBook->update([
                        'type' => $phoneData['type'] ?? $phoneBook->type,
                        'country_code' => $phoneData['country_code'] ?? $phoneBook->country_code,
                        'phone_number' => $phoneData['phone_number'] ?? $phoneBook->phone_number,
                        'description' => $phoneData['description'] ?? $phoneBook->description,
                        'status' => $phoneData['status'] ?? $phoneBook->status,
                        'is_default' => $phoneData['is_default'] ?? $phoneBook->is_default,
                        'verified_at' => $phoneData['verified_at'] ?? $phoneBook->verified_at,
                        'alternative_relation' => $phoneData['alternative_relation'] ?? $phoneBook->alternative_relation,
                        'alternative_contact' => $phoneData['alternative_contact'] ?? $phoneBook->alternative_contact,
                        'mfs_operator' => $phoneData['mfs_operator'] ?? $phoneBook->mfs_operator,
                        'mfs_number' => $phoneData['mfs_number'] ?? $phoneBook->mfs_number,
                        'updated_by' => $authId,
                    ]);
                }
            }

            log_activity($user->id, 'Updated client phonebook', authId(), 'client', 'updated', [
                'id' => $user->id,

            ]);

            DB::commit();
            $user->load(['phoneBooks']);

            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            log_activity($user->id, 'PhoneBooks update failed:', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    public function destroy(string $id): void
    {
        $client = $this->show($id);
        $client->delete();

        log_activity(null, 'Deleted Client', authId(), 'client', 'deleted', $client->toArray());
    }

    public function search(array $filters)
    {
        $query = $this->model->newQuery();

        if (isset($filters['search']) && ! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('mobile', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->latest()->take($filters['limit'] ?? 10)->get();
    }

    public function sendOtp(array $attributes): array
    {

        $otp = random_int(100000, 999999); // safer than rand()
        $minutes = 5;
        $expiry = now()->addMinutes($minutes);
        $contact = $attributes['contact'];
        $type = $attributes['type'];

        // Cache OTP for later verification
        $cacheKey = 'otp:' . Str::slug($contact);
        Cache::put($cacheKey, [
            'otp' => $otp,
            'expires_at' => $expiry,
        ], $minutes * 60);

        $routing = [];
        if ($type == 'email') {
            $routing['email'] = [
                'to' => $contact,
            ];
        } elseif ($type == 'sms') {
            $routing['sms'] = [
                'to' => $contact,
                'provider' => 'ssl',
            ];
        } elseif ($type == 'whatsapp') {
            $routing['whatsapp'] = [
                'to' => $contact,
            ];
        }

        $payload = [
            'channels' => [$type],
            'notification_type' => 'otp',
            'data' => [
                'otp' => $otp,
                'contact' => $contact,
                'minutes' => $minutes,
                'expiry' => $expiry->toDateTimeString(),
            ],
            'template' => [
                'sms' => 'Your OTP is :otp. It will expire in :minutes minutes.',
                'whatsapp' => 'Your OTP is :otp. It will expire in :minutes minutes.',
                'email' => [
                    'subject' => 'Your OTP Code',
                    'body' => 'Your OTP is :otp. It will expire in :minutes minutes.',
                ],
            ],
            'routing' => $routing,
            'meta' => [
                'source' => 'auth-service',
                'event' => 'send_otp',
                'triggered_at' => now()->toDateTimeString(),
                'triggered_by' => 'system',
            ],
        ];

        // Notification service endpoint
        $tokens = app(MachineTokenManager::class);
        $base = (string) data_get(config('gateway.services'), 'notification_service.base_uri');
        abort_unless($base, 500, 'notification_service.base_uri not configured');
        $endpoint = rtrim($base, '/') . '/v1/notification-service/notifications';
        $aud = (string) data_get(config('gateway.services'), 'log_service.token_service', 'log-service');
        $token = $tokens->get($aud);

        // HTTP call with auth + timeout + error handling
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->retry(2, 1000)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'expires_at' => $expiry->toDateTimeString(),
                    'minutes' => $minutes,
                    'message' => "OTP sent to {$contact}.",
                    'contact' => $contact,
                    'type' => $type,
                ];
            }

            return [
                'status' => false,
                'message' => "Failed to send OTP to {$contact}.",
                'contact' => $contact,
                'type' => $type,
            ];
        } catch (\Throwable $e) {
            Log::error('OTP send failed: ' . $e->getMessage(), ['exception' => $e]);

            return [
                'status' => false,
                'message' => "Failed to send OTP to {$contact}.",
                'contact' => $contact,
                'type' => $type,
            ];
        }
    }

    public function verifyOtp(array $attributes): bool|array
    {
        $contact = $attributes['contact'];
        $inputOtp = $attributes['otp'];

        $cacheKey = 'otp:' . Str::slug($contact);
        $cachedData = Cache::get($cacheKey);
        if (! $cachedData) {
            return [
                'status' => false,
                'message' => 'OTP not found or expired. Please request a new one.',
                'contact' => $contact,
                'type' => $attributes['type'],
            ];
        }

        if ($cachedData['otp'] != $inputOtp) {
            return [
                'status' => false,
                'message' => 'Invalid OTP. Please try again.',
                'contact' => $contact,
                'type' => $attributes['type'],
            ];
        }

        // OTP is valid, remove from cache
        Cache::forget($cacheKey);

        return [
            'status' => true,
            'message' => 'OTP verified successfully.',
            'contact' => $contact,
            'type' => $attributes['type'],
        ];
    }

    public function getClientsWithoutConnection(array $queries)
    {
        $directoryDb = DB::connection('directory')->getDatabaseName();

        $query = User::role('client')
            ->leftJoin("{$directoryDb}.user_network_indices as uni", 'uni.user_id', '=', 'users.id')
            ->where(function ($q) {
                // No connection at all
                $q->whereNull('uni.user_id')
                    // Or connection exists but missing cid or next_cycle
                    ->orWhereNull('uni.cid')
                    ->orWhereNull('uni.next_cycle');
            });

        // 🔍 Optional search
        if (! empty($queries['search'])) {
            $search = $queries['search'];
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.mobile', 'like', "%{$search}%");
            });
        }

        // 🏠 Load related data
        $query->with(['addressBooks']);

        // 📄 Pagination
        $perPage = isset($queries['per_page']) ? (int) $queries['per_page'] : 10;
        $page = isset($queries['page']) ? (int) $queries['page'] : 1;

        // 🧾 Select required fields including cid and next_cycle
        $query->select([
            'users.*',
            'uni.cid',
            'uni.next_cycle',
            'uni.network_id',
            'uni.package_id',
        ]);

        return $query->latest('users.id')->paginate($perPage, ['*'], 'page', $page);
    }



public function getClientInformation(string $userId, string $networkId): array
{
    try {


        $user = User::whereHas('networks', function ($query) use ($userId, $networkId) {
            $query->where('user_id', $userId)
                  ->where('network_id', $networkId);
        })
        ->with([
            'client.userGroup',
            'addressBooks.country',
            'addressBooks.state',
            'addressBooks.city',
            'userIdentities',
            'phoneBooks'
        ])
        ->firstOrFail();

        $phone = $user->phoneBooks->first();
        $address = $user->addressBooks->first();
        $identity = $user->userIdentities->first();
        $client = $user->client;

        return [
            'personal_information' => [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->mobile,
                'category' => $client?->userGroup?->name,
                'date_of_birth' => $client?->dob,
                'national_id' => $identity?->identity_number,
            ],
            'contact_details' => [
                'alternative_contact' => $phone?->alternative_contact,
                'alternative_relation' => $phone?->alternative_relation,
                'mfs_operator' => $phone?->mfs_operator,
                'mfs_number' => $phone?->mfs_number,
            ],
            'address_information' => [
                'address' => $address?->address,
                'house_no' => $address?->house_no,
                'road_no' => $address?->road_no,
                'city' => $address?->city?->name,
                'state' => $address?->state?->name,
                'country' => $address?->country?->name,
                'post_code' => $address?->postal_code,
                'coordinates' => $address?->latitude && $address?->longitude
                    ? "{$address->latitude},{$address->longitude}" : null,
            ],
            'identity_verification' => [
                'identification_type' => $identity?->identity_type,
                'identification_no' => $identity?->identity_number,
                'frontside' => $identity?->document_1,
                'backside' => $identity?->document_2,
            ]
        ];
    } catch (\Throwable $e) {
        log_activity($userId, 'Get client info failed', authId(), 'client', 'failed', ['error' => $e->getMessage()], 'error');
        throw $e;
    }
}


}
