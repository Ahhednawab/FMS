<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The supervisors allowed to send attendance over WhatsApp.
 *
 * Kept separate from `users` on purpose: a supervisor sends attendance from a
 * phone but does not necessarily have a login, and the number is the only thing
 * the webhook can identify them by.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_senders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);

            // Digits only, in full international form (923001234567), which is
            // what Meta sends in the webhook payload.
            $table->string('whatsapp_number', 32)->unique();

            // stations.id is int(11), so this matches it rather than using a
            // bigint that could never be constrained against it.
            $table->integer('station_id')->nullable()->index();

            $table->boolean('is_active')->default(true)->index();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_senders');
    }
};
