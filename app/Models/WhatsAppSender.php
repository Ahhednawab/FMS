<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A supervisor allowed to send attendance over WhatsApp.
 *
 * The number is the only credential the webhook has, so a sender row is what
 * separates a real supervisor from anyone who finds the number.
 */
class WhatsAppSender extends Model
{
    protected $table = 'whatsapp_senders';

    protected $fillable = [
        'name',
        'whatsapp_number',
        'station_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_sender_id');
    }

    /**
     * Reduce a number to digits so the stored value and whatever Meta sends
     * compare equal. "+92 300 1234567" and "923001234567" are the same number,
     * and a leading 0 is replaced by the Pakistan country code.
     */
    public static function normalizeNumber(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '92' . substr($digits, 1);
        }

        return $digits;
    }

    /** The active sender behind a number, or null if the number is unknown. */
    public static function findByNumber(?string $number): ?self
    {
        $normalized = static::normalizeNumber($number);

        if ($normalized === '') {
            return null;
        }

        return static::where('whatsapp_number', $normalized)
            ->where('is_active', true)
            ->first();
    }
}
