<?php

namespace App\Console\Commands;

use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\AttendanceDraftBuilder;
use Illuminate\Console\Command;
use Throwable;

/**
 * Reads any message that arrived but never got turned into review rows.
 *
 * The webhook normally parses a message straight after answering Meta, so this
 * is the safety net: a PHP process that died mid-request, or a queue worker
 * that was not running, leaves messages sitting in the `received` state. The
 * raw message is always on record, so nothing is lost - it just needs reading.
 *
 *   php artisan whatsapp:parse-pending
 *   php artisan whatsapp:parse-pending --failed    (retry the ones that threw)
 */
class ParsePendingWhatsAppMessages extends Command
{
    protected $signature = 'whatsapp:parse-pending
                            {--failed : Also retry messages that previously failed}
                            {--limit=200 : How many messages to read in one run}';

    protected $description = 'Turn received WhatsApp messages into attendance drafts awaiting review';

    public function handle(AttendanceDraftBuilder $builder): int
    {
        $states = [WhatsAppMessage::STATE_RECEIVED];

        if ($this->option('failed')) {
            $states[] = WhatsAppMessage::STATE_FAILED;
        }

        $messages = WhatsAppMessage::with('sender')
            ->whereIn('state', $states)
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($messages->isEmpty()) {
            $this->info('Nothing waiting.');

            return self::SUCCESS;
        }

        $drafts = 0;
        $failed = 0;

        foreach ($messages as $message) {
            try {
                $drafts += $builder->build($message);
            } catch (Throwable $e) {
                $failed++;
                $message->markState(WhatsAppMessage::STATE_FAILED, $e->getMessage());
                $this->error('Message ' . $message->id . ': ' . $e->getMessage());
            }
        }

        $this->info($messages->count() . ' message(s) read, ' . $drafts . ' line(s) queued for review.');

        if ($failed > 0) {
            $this->warn($failed . ' message(s) could not be read and are marked failed.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
