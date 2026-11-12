<?php

namespace App\Http\Controllers;

use App\Jobs\ParseWhatsAppAttendance;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSender;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * The public endpoint the WhatsApp Cloud API posts to.
 *
 * This is the only route in the application that no login protects, so it is
 * written to be suspicious of everything:
 *
 *   - while WHATSAPP_APP_SECRET is unset it refuses every call, so a
 *     half-finished setup cannot quietly accept unsigned requests;
 *   - every POST must carry a valid X-Hub-Signature-256 over the exact raw
 *     body, compared with hash_equals;
 *   - a message is stored under Meta's own id, so the retries that follow a
 *     missed 200 cannot create the same attendance twice;
 *   - nothing is interpreted here. The response goes back first and the
 *     message is read afterwards.
 *
 * Deciding whether a message is real attendance, and whether it becomes
 * attendance at all, happens later and with a person in the loop.
 */
class WhatsAppWebhookController extends Controller
{
    /**
     * Meta calls this once, with the verify token, while the webhook URL is
     * being set up in the dashboard. Echoing the challenge back completes it.
     */
    public function verify(Request $request)
    {
        $expected = (string) config('whatsapp.verify_token');

        if ($expected === '') {
            Log::warning('WhatsApp webhook verification attempted before WHATSAPP_VERIFY_TOKEN was set.');

            return response('Webhook not configured', 503);
        }

        $mode = (string) $request->query('hub_mode', $request->query('hub.mode', ''));
        $token = (string) $request->query('hub_verify_token', $request->query('hub.verify_token', ''));
        $challenge = (string) $request->query('hub_challenge', $request->query('hub.challenge', ''));

        if ($mode === 'subscribe' && hash_equals($expected, $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /** Every incoming message and status update arrives here. */
    public function receive(Request $request)
    {
        $secret = (string) config('whatsapp.app_secret');

        if ($secret === '') {
            Log::warning('WhatsApp webhook called before WHATSAPP_APP_SECRET was set; request rejected.');

            return response()->json(['error' => 'not configured'], 503);
        }

        if (!$this->signatureIsValid($request, $secret)) {
            Log::warning('WhatsApp webhook signature rejected', ['ip' => $request->ip()]);

            return response()->json(['error' => 'invalid signature'], 403);
        }

        // From here on the request is trusted to have come from Meta, so it
        // always gets a 200 - anything else and the same payload comes back.
        try {
            $this->store($request->json()->all());
        } catch (\Throwable $e) {
            Log::error('WhatsApp webhook could not be stored', ['exception' => $e->getMessage()]);
        }

        return response()->json(['received' => true], 200);
    }

    /**
     * Was this body signed with the app secret?
     *
     * The HMAC is over the raw body exactly as it arrived, so the request
     * content is used rather than anything re-encoded from the parsed JSON.
     */
    private function signatureIsValid(Request $request, string $secret): bool
    {
        $header = (string) $request->header('X-Hub-Signature-256', '');

        if (!str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $header);
    }

    /**
     * Pull the text messages out of the payload and queue each one to be read.
     *
     * Status updates (delivered, read) and other kinds of change arrive on the
     * same webhook and are simply skipped.
     */
    private function store(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                if (($change['field'] ?? null) !== 'messages' || !isset($value['messages'])) {
                    continue;
                }

                $profileNames = $this->profileNames($value['contacts'] ?? []);

                foreach ($value['messages'] as $message) {
                    $this->storeMessage($message, $profileNames);
                }
            }
        }
    }

    /**
     * @param  array<string, string>  $profileNames
     */
    private function storeMessage(array $message, array $profileNames): void
    {
        $waMessageId = $message['id'] ?? null;
        $from = (string) ($message['from'] ?? '');

        if ($from === '') {
            return;
        }

        // Only text carries attendance. An image or voice note is stored with
        // an empty body so the review screen can still show that it arrived.
        $body = $message['text']['body'] ?? null;

        // Meta delivers the same message again whenever it does not get a 200,
        // so an id that is already on record is left exactly as it is.
        if ($waMessageId !== null && WhatsAppMessage::where('wa_message_id', $waMessageId)->exists()) {
            return;
        }

        $sender = WhatsAppSender::findByNumber($from);

        $record = WhatsAppMessage::create([
            'wa_message_id' => $waMessageId,
            'from_number' => WhatsAppSender::normalizeNumber($from),
            'whatsapp_sender_id' => $sender?->id,
            'wa_profile_name' => $profileNames[$from] ?? null,
            'body' => $body,
            'payload' => $message,
            'received_at' => isset($message['timestamp'])
                ? Carbon::createFromTimestamp((int) $message['timestamp'])
                : now(),
            'state' => WhatsAppMessage::STATE_RECEIVED,
        ]);

        if ($body === null) {
            $record->markState(
                WhatsAppMessage::STATE_IGNORED,
                'Message type "' . ($message['type'] ?? 'unknown') . '" carries no text.'
            );

            return;
        }

        // After the response by default, so no queue worker is required. Set
        // WHATSAPP_PARSE_QUEUE=true on a host that runs `queue:work`.
        $job = new ParseWhatsAppAttendance($record->id);

        if (config('whatsapp.parse_queue')) {
            dispatch($job);

            return;
        }

        dispatch($job)->afterResponse();
    }

    /**
     * The WhatsApp display names in the payload, keyed by number.
     *
     * @return array<string, string>
     */
    private function profileNames(array $contacts): array
    {
        $names = [];

        foreach ($contacts as $contact) {
            $waId = $contact['wa_id'] ?? null;
            $name = $contact['profile']['name'] ?? null;

            if ($waId !== null && $name !== null) {
                $names[(string) $waId] = (string) $name;
            }
        }

        return $names;
    }
}
