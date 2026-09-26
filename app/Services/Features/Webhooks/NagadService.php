<?php

namespace App\Services\Features\Webhooks;

use Illuminate\Support\Facades\Http;

class NagadService
{
    //     curl --location 'https://care.saltsync.com/api/nagad-webhook' \
    // --header 'Accept: application/json' \
    // --header 'Content-Type: application/json' \
    // --data '{
    //     "transactionReference":"3000",
    //     "trxID":"012356",
    //     "amount":"4",
    //     "paidNumber":"01789594722",
    //     "merchantNumber":"01404079554"
    // }'
    public function handleNagadWebhook(array $attributes)
    {
        return Http::post('https://care.saltsync.com/api/nagad-webhook', $attributes);
    }
}
