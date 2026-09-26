<?php

namespace App\Jobs\Logging;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Applications\Logging\RemoteLogClient;

class FlushBufferedLogs implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $bufferKey) {}

    public function handle(RemoteLogClient $client)
    {
        $maxIterations = config('logging.client.flush_iterations', 5);
        $batchSize = config('logging.client.batch_size', 200);

        for ($i = 0; $i < $maxIterations; $i++) {
            $success = $client->flush($batchSize);
            if (! $success) {
                Log::warning('FlushBufferedLogs: flush returned false; stopping iteration');
                break;
            }
            $remaining = \Illuminate\Support\Facades\Redis::llen($this->bufferKey) ?? 0;
            if ($remaining <= 0) break;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('FlushBufferedLogs job failed', ['err' => $exception->getMessage()]);
    }
}
