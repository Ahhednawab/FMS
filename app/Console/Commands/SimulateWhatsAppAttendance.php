<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSender;
use App\Services\WhatsApp\AttendanceDraftBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Feed a message through the parser as though it had arrived on WhatsApp.
 *
 * This is how the review screen can be tried before there is a Meta account:
 * it writes the same `whatsapp_messages` row the webhook would and queues the
 * same drafts, so the whole review-and-confirm flow works end to end. Only the
 * webhook itself is skipped.
 *
 *   php artisan whatsapp:simulate
 *   php artisan whatsapp:simulate --text="ATT 12-11-2026
 *   JZ-9036 P"
 *   php artisan whatsapp:simulate --file=storage/app/att.txt
 *   php artisan whatsapp:simulate --dry-run
 */
class SimulateWhatsAppAttendance extends Command
{
    protected $signature = 'whatsapp:simulate
                            {--from= : Sender number; defaults to the first active supervisor}
                            {--text= : The message body}
                            {--file= : Read the message body from a file instead}
                            {--dry-run : Show what would happen without writing anything}';

    protected $description = 'Pretend a supervisor sent an attendance message, to try the review screen';

    public function handle(AttendanceDraftBuilder $builder): int
    {
        $sender = $this->resolveSender();

        if ($sender === null) {
            return self::FAILURE;
        }

        $body = $this->resolveBody();

        if ($body === null) {
            return self::FAILURE;
        }

        $this->line('From: ' . $sender->name . ' (' . $sender->whatsapp_number . ')');
        $this->newLine();
        $this->line($body);
        $this->newLine();

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            DB::beginTransaction();
        }

        try {
            $message = WhatsAppMessage::create([
                // Marked so a simulated message is never mistaken for a real one.
                'wa_message_id' => 'simulated.' . uniqid(),
                'from_number' => $sender->whatsapp_number,
                'whatsapp_sender_id' => $sender->id,
                'wa_profile_name' => $sender->name,
                'body' => $body,
                'payload' => ['simulated' => true],
                'received_at' => now(),
                'state' => WhatsAppMessage::STATE_RECEIVED,
            ]);

            $created = $builder->build($message);
            $message->refresh();

            $this->info($created . ' line(s) queued for review. Message state: ' . $message->state);

            if ($message->error) {
                $this->warn($message->error);
            }

            if ($created > 0) {
                $this->newLine();
                $this->table(
                    ['Line', 'Sent', 'Date', 'Driver', 'Status', 'Problem'],
                    $message->drafts()->with(['driver', 'attendanceStatus'])->orderBy('line_no')->get()
                        ->map(fn ($draft) => [
                            $draft->line_no,
                            $draft->raw_line,
                            $draft->attendance_date?->format('d-m-Y') ?? '-',
                            $draft->driver->full_name ?? '-',
                            $draft->attendanceStatus->name ?? '-',
                            $draft->error ?? 'ok',
                        ])->all()
                );

                $this->line('Open Attendance > WhatsApp Attendance to review and confirm them.');
            }
        } finally {
            if ($dryRun) {
                DB::rollBack();
                $this->newLine();
                $this->warn('--dry-run: rolled back, nothing was saved.');
            }
        }

        return self::SUCCESS;
    }

    private function resolveSender(): ?WhatsAppSender
    {
        $from = $this->option('from');

        if ($from) {
            $sender = WhatsAppSender::findByNumber($from);

            if (!$sender) {
                $this->error('No active supervisor on ' . $from
                    . '. Add one with: php artisan whatsapp:add-sender "Name" ' . $from);

                return null;
            }

            return $sender;
        }

        $sender = WhatsAppSender::where('is_active', true)->orderBy('id')->first();

        if (!$sender) {
            $this->error('No supervisors registered yet.');
            $this->line('Add one first: php artisan whatsapp:add-sender "Imran Johar" 03001234567');

            return null;
        }

        return $sender;
    }

    private function resolveBody(): ?string
    {
        if ($file = $this->option('file')) {
            if (!is_file($file)) {
                $this->error('No such file: ' . $file);

                return null;
            }

            return (string) file_get_contents($file);
        }

        if ($text = $this->option('text')) {
            return (string) $text;
        }

        return $this->sampleMessage();
    }

    /**
     * A sample in the shape the supervisors really use - bullets, the driver
     * name beside the vehicle, and the P emoji for the status - built from
     * this database's own vehicles so the review screen shows real people.
     *
     * The names are written the way a supervisor would write them, short and
     * phonetic, so the matching is exercised rather than sidestepped.
     */
    private function sampleMessage(): string
    {
        $present = "\u{1F17F}\u{FE0F}";
        $absent = "\u{1F170}\u{FE0F}";

        $drivers = Driver::query()
            ->where('is_active', 1)
            ->whereNotNull('vehicle_id')
            ->where('vehicle_id', '>', 0)
            ->orderBy('id')
            ->limit(4)
            ->get(['id', 'full_name', 'vehicle_id']);

        if ($drivers->isEmpty()) {
            return "Sample attendence\n1•JZ 9036 shahinsha " . $present;
        }

        $lines = ['Sample station driver attendence'];
        $index = 0;

        foreach ($drivers as $driver) {
            $number = Vehicle::where('id', $driver->vehicle_id)->value('vehicle_no');

            if (!$number) {
                continue;
            }

            $index++;
            $lines[] = $index . '•' . $number . ' ' . $this->asTyped($driver->full_name) . ' ' . $present;
        }

        // The cases a reviewer has to decide: a stand-in, a vehicle nobody
        // knows, and a name that matches nobody.
        $standIn = $drivers->first();
        $standInNumber = Vehicle::where('id', $standIn->vehicle_id)->value('vehicle_no');

        $lines[] = ++$index . '•' . $standInNumber . ' ' . $this->asTyped($standIn->full_name)
            . ' ' . $absent . ' Imran akhtr ' . $present;
        $lines[] = ++$index . '•XX 0000 someone ' . $present;
        $lines[] = 'Thanks sir';

        return implode("\n", $lines);
    }

    /** Shorten a registered name the way a supervisor would type it. */
    private function asTyped(string $fullName): string
    {
        $words = preg_split('/\s+/', trim($fullName)) ?: [$fullName];
        $words = array_values(array_filter(
            $words,
            static fn (string $word) => !in_array(strtolower($word), ['muhammad', 'mohammad', 'syed'], true)
        ));

        return strtolower($words[0] ?? $fullName);
    }
}
