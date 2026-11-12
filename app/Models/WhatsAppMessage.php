<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One raw message as it arrived from the Cloud API.
 *
 * Nothing is interpreted here. Whatever FMS later decides the message meant,
 * `body` and `payload` still hold exactly what was sent.
 */
class WhatsAppMessage extends Model
{
    public const STATE_RECEIVED = 'received';
    public const STATE_PARSED = 'parsed';
    public const STATE_IGNORED = 'ignored';
    public const STATE_FAILED = 'failed';

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'wa_message_id',
        'from_number',
        'whatsapp_sender_id',
        'wa_profile_name',
        'body',
        'payload',
        'received_at',
        'state',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(WhatsAppSender::class, 'whatsapp_sender_id');
    }

    public function drafts()
    {
        return $this->hasMany(WhatsAppAttendanceDraft::class, 'whatsapp_message_id');
    }

    /** Record why a message produced nothing, without losing the message. */
    public function markState(string $state, ?string $error = null): void
    {
        $this->update(['state' => $state, 'error' => $error]);
    }
}
