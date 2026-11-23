<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpBillingNotices
{
    public function forBranch(?int $branchId): array
    {
        $baseUrl = rtrim((string) config('services.erp.base_url'), '/');
        $secret = (string) config('services.erp.integration_secret');
        if (!$branchId || !$baseUrl || !$secret) return [];

        $payload = ['branch_id' => (string) $branchId];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp."\n".$body, $secret);
        try {
            $response = Http::timeout(5)->withHeaders([
                'X-SaaS-Timestamp' => $timestamp,
                'X-SaaS-Signature' => $signature,
            ])->withBody($body, 'application/json')->post($baseUrl.'/api/integrations/hotelhub/notices');
            if (!$response->successful()) {
                Log::warning('ERP billing notices returned a non-success status', ['status' => $response->status()]);
                return [];
            }
            return is_array($response->json('notices')) ? $response->json('notices') : [];
        } catch (\Throwable $exception) {
            Log::warning('ERP billing notices are temporarily unavailable', ['exception' => $exception->getMessage()]);
            return [];
        }
    }
}
