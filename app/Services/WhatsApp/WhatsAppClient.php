<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends a message back to a supervisor.
 *
 * Only used for short acknowledgements ("got 14 lines for 12-11-2026"), and
 * only inside the 24-hour window that opens when they message first - outside
 * that window Meta requires an approved template instead.
 *
 * Replies are off by default (`WHATSAPP_REPLY_ON_RECEIPT`) because each one is
 * a billable message, and the review screen is the real confirmation anyway.
 */
class WhatsAppClient
{
    /** Whether the credentials needed to send anything are present. */
    public function isConfigured(): bool
    {
        return filled(config('whatsapp.token')) && filled(config('whatsapp.phone_number_id'));
    }

    /**
     * Send a plain text reply. Returns false rather than throwing: a failed
     * courtesy reply must never break the attendance it was acknowledging.
     */
    public function sendText(string $toNumber, string $message): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            config('whatsapp.api_version'),
            config('whatsapp.phone_number_id')
        );

        try {
            $response = Http::withToken(config('whatsapp.token'))
                ->timeout(15)
                ->asJson()
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $toNumber,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->failed()) {
                Log::warning('WhatsApp reply rejected', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp reply failed', ['exception' => $e->getMessage()]);

            return false;
        }
    }
}
