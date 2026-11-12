<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every message the webhook receives, stored before anything is interpreted.
 *
 * This is the audit trail: whatever FMS later decides a message meant, the
 * original text is still here. The unique `wa_message_id` is also what makes
 * Meta retries harmless - the same message cannot be recorded twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();

            // Meta own id for the message (wamid...). Nullable only so a
            // message typed in by hand for testing can still be stored.
            $table->string('wa_message_id', 191)->nullable()->unique();

            $table->string('from_number', 32)->index();
            $table->foreignId('whatsapp_sender_id')->nullable()->index();
            $table->string('wa_profile_name', 120)->nullable();

            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->nullable()->index();

            // received -> parsed | ignored | failed
            $table->string('state', 20)->default('received')->index();
            $table->text('error')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
