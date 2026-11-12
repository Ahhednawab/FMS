<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The review queue: one row per attendance line a supervisor sent.
 *
 * A draft is not attendance. It holds what was typed, what FMS thinks it means,
 * and what went wrong if anything did. Only when somebody confirms a draft is a
 * `drivers_attendances` row written, and `drivers_attendance_id` then points at it.
 *
 * vehicle_id / driver_id carry no foreign keys on purpose: `drivers.vehicle_id`
 * is int(11) while `vehicles.id` is bigint unsigned, and the existing tables
 * already live with that mismatch. A constraint here would fail on some rows
 * without making anything safer, since a draft is allowed to point nowhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_attendance_drafts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('whatsapp_message_id')
                ->constrained('whatsapp_messages')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('line_no')->default(0);
            $table->string('raw_line', 255);

            // What the parser read out of the line.
            $table->date('attendance_date')->nullable()->index();
            $table->string('vehicle_token', 60)->nullable();
            $table->string('status_token', 30)->nullable();

            // What it resolved to. Null means the reviewer has to choose.
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->tinyInteger('status')->nullable();

            // pending -> confirmed | rejected
            $table->string('state', 20)->default('pending')->index();
            $table->string('error', 255)->nullable();

            $table->unsignedBigInteger('drivers_attendance_id')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            // The review screen is always filtered by date and state.
            $table->index(['attendance_date', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_attendance_drafts');
    }
};
