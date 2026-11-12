<?php

namespace App\Console\Commands;

use App\Models\Station;
use App\Models\WhatsAppSender;
use Illuminate\Console\Command;

/**
 * Register a supervisor who may send attendance over WhatsApp.
 *
 * Until a number is on this list its messages are stored and flagged but never
 * turned into drafts, so this is the first thing to run.
 *
 *   php artisan whatsapp:add-sender "Imran Johar" 03001234567 --station=3
 *   php artisan whatsapp:add-sender --list
 */
class AddWhatsAppSender extends Command
{
    protected $signature = 'whatsapp:add-sender
                            {name? : The supervisor name}
                            {number? : Their WhatsApp number, any format}
                            {--station= : stations.id this supervisor covers}
                            {--list : Show the registered senders and the station ids}
                            {--disable= : Deactivate a number instead of adding one}';

    protected $description = 'Register, list or disable a WhatsApp attendance supervisor';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listSenders();
        }

        if ($number = $this->option('disable')) {
            return $this->disable($number);
        }

        $name = $this->argument('name');
        $number = $this->argument('number');

        if (!$name || !$number) {
            $this->error('Give a name and a number, or use --list.');

            return self::FAILURE;
        }

        $normalized = WhatsAppSender::normalizeNumber($number);

        if (strlen($normalized) < 10) {
            $this->error('"' . $number . '" does not look like a phone number.');

            return self::FAILURE;
        }

        $stationId = $this->option('station');

        if ($stationId !== null && !Station::where('id', $stationId)->exists()) {
            $this->error('No station with id ' . $stationId . '. Run --list to see them.');

            return self::FAILURE;
        }

        $sender = WhatsAppSender::updateOrCreate(
            ['whatsapp_number' => $normalized],
            [
                'name' => $name,
                'station_id' => $stationId === null ? null : (int) $stationId,
                'is_active' => true,
            ]
        );

        $this->info(($sender->wasRecentlyCreated ? 'Added ' : 'Updated ')
            . $sender->name . ' (' . $normalized . ').');

        if ($stationId === null) {
            $this->line('No station set, so this supervisor can mark any driver.');
        }

        return self::SUCCESS;
    }

    private function listSenders(): int
    {
        $senders = WhatsAppSender::with('station')->orderBy('name')->get();

        if ($senders->isEmpty()) {
            $this->warn('No supervisors registered yet. Nothing sent on WhatsApp will be read.');
        } else {
            $this->table(
                ['Name', 'Number', 'Station', 'Active'],
                $senders->map(fn (WhatsAppSender $sender) => [
                    $sender->name,
                    $sender->whatsapp_number,
                    $sender->station->area ?? '(any)',
                    $sender->is_active ? 'yes' : 'no',
                ])->all()
            );
        }

        $this->newLine();
        $this->line('Stations:');
        $this->table(
            ['id', 'Area'],
            Station::where('is_active', 1)->orderBy('id')->get()
                ->map(fn (Station $station) => [$station->id, $station->area])->all()
        );

        return self::SUCCESS;
    }

    private function disable(string $number): int
    {
        $normalized = WhatsAppSender::normalizeNumber($number);
        $updated = WhatsAppSender::where('whatsapp_number', $normalized)->update(['is_active' => false]);

        if ($updated === 0) {
            $this->error('No supervisor registered on ' . $normalized . '.');

            return self::FAILURE;
        }

        $this->info($normalized . ' can no longer post attendance.');

        return self::SUCCESS;
    }
}
