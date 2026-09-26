<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Features\Webhooks\NagadService;
use Illuminate\Http\Request;

class NagadController extends Controller
{
    public function __construct(protected NagadService $nagadService) {}

    public function handleNagadWebhook(Request $request)
    {
        $validated = $request->validate([
            'transactionReference' => 'required|string',
            'trxID' => 'required|string',
            'amount' => 'required|numeric',
            'paidNumber' => 'required|string',
            'merchantNumber' => 'required|string',
        ]);

        return $this->nagadService->handleNagadWebhook($validated);
    }
}
