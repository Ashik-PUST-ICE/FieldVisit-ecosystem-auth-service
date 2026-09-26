<?php

namespace App\Services\Applications\Logging;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\Logging\FlushBufferedLogs;

class RemoteLogClient
{
    protected string $endpoint;
    protected ?string $apiKey;
    protected string $bufferKey;
    protected int $batchSize;
    protected int $flushThreshold;
    protected int $timeout;
    protected int $maxRetries;
    protected int $retryDelayMs;

    public function __construct()
    {
        $this->endpoint = rtrim(config('gateway.services.log_service.base_uri', ''), '/') . '/v1/ingest/bulk';
        $this->apiKey = data_get(config('gateway.services.log_service'), 'api_key', env('LOG_SERVICE_API_KEY'));
        $this->bufferKey = 'remote_log_buffer:' . (config('app.name') ?? 'app') . ':' . gethostname();
        $this->batchSize = config('logging.client.batch_size', 200);
        $this->flushThreshold = config('logging.client.flush_threshold', 50);
        $this->timeout = config('logging.client.timeout', 5);
        $this->maxRetries = config('logging.client.retries', 3);
        $this->retryDelayMs = config('logging.client.retry_delay_ms', 200);
    }

    public function push(array $event): void
    {
        try {
            \Illuminate\Support\Facades\Redis::rpush($this->bufferKey, json_encode($event));
            $max = config('logging.client.buffer_max', 5000);
            \Illuminate\Support\Facades\Redis::ltrim($this->bufferKey, -$max, -1);

            $len = \Illuminate\Support\Facades\Redis::llen($this->bufferKey);
            if ($len >= $this->flushThreshold) {
                FlushBufferedLogs::dispatch($this->bufferKey)->onQueue('logs');
            }
        } catch (Throwable $e) {
            Log::warning('RemoteLogClient: redis unavailable', ['err' => $e->getMessage()]);
            file_put_contents(storage_path('logs/remote_log_buffer.jsonl'), json_encode($event) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    public function flush(?int $limit = null): bool
    {
        $limit = $limit ?? $this->batchSize;

        try {
            $script = <<<'LUA'
local key = KEYS[1]
local n = tonumber(ARGV[1])
local res = {}
for i = 1, n do
  local v = redis.call('lpop', key)
  if not v then break end
  table.insert(res, v)
end
return res
LUA;
            $items = \Illuminate\Support\Facades\Redis::eval($script, 1, $this->bufferKey, $limit);
        } catch (Throwable $e) {
            Log::error('RemoteLogClient: redis lua pop failed', ['err' => $e->getMessage()]);
            return false;
        }

        if (empty($items)) {
            return true;
        }

        $events = array_map(fn($i) => json_decode($i, true), $items);

        $attempt = 0;
        $delay = $this->retryDelayMs;
        while ($attempt < $this->maxRetries) {
            $attempt++;
            try {
                $http = Http::timeout($this->timeout);

                if ($this->apiKey) {
                    $http = $http->withHeaders(['X-API-KEY' => $this->apiKey]);
                }

                $resp = $http->post($this->endpoint, $events);

                if ($resp->successful() || $resp->status() === 202) {
                    return true;
                }

                if (in_array($resp->status(), [429, 500, 502, 503, 504])) {
                    usleep($delay * 1000);
                    $delay *= 2;
                    continue;
                }

                Log::warning('RemoteLogClient: non-retriable response', ['status' => $resp->status()]);
                break;
            } catch (Throwable $e) {
                usleep($delay * 1000);
                $delay *= 2;
            }
        }

        foreach ($events as $ev) {
            file_put_contents(storage_path('logs/remote_log_fallback.jsonl'), json_encode($ev) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        return false;
    }

    public function sendNow(array $events): bool
    {
        try {
            $http = Http::timeout($this->timeout);
            if ($this->apiKey) $http = $http->withHeaders(['X-API-KEY' => $this->apiKey]);
            $resp = $http->post($this->endpoint, $events);

            return $resp->successful() || $resp->status() === 202;
        } catch (Throwable $e) {
            Log::error('RemoteLogClient: sendNow failure', ['err' => $e->getMessage()]);
            return false;
        }
    }
}
