<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\AttendanceDraftBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reads one received message and queues its lines for review.
 *
 * Kept out of the webhook request so the Cloud API gets its 200 straight away:
 * if Meta does not get that 200 it delivers the same message again, and again.
 *
 * By default the webhook runs this with dispatchAfterResponse(), which needs no
 * queue worker - useful on a host where nothing runs `queue:work`. Set
 * WHATSAPP_PARSE_QUEUE=true to put it on the real queue instead.
 */
class ParseWhatsAppAttendance implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $messageId)
    {
    }

    public function handle(AttendanceDraftBuilder $builder): void
    {
        $message = WhatsAppMessage::with('sender')->find($this->messageId);

        if (!$message) {
            return;
        }

        try {
            $builder->build($message);
        } catch (Throwable $e) {
            // The message itself is never lost: it keeps its raw body and the
            // reason it could not be read, so it can be retried or fixed later.
            $message->markState(WhatsAppMessage::STATE_FAILED, $e->getMessage());

            Log::error('WhatsApp attendance parse failed', [
                'whatsapp_message_id' => $message->id,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
