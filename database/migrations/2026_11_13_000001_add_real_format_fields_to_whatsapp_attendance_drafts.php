<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widen the review queue to hold what supervisors really send.
 *
 * The first version of this table assumed a message was "vehicle number plus
 * status code". The actual registers carry a driver name on every line, mark
 * a stand-in on the same line as the man he covered, run a separate register
 * for vehicles, and sometimes send no status at all.
 *
 * None of that can be flattened without losing the thing the reviewer needs
 * to see, so each part gets its own column:
 *
 *   driver_name_token   what the supervisor actually typed
 *   matched_by          how the driver was arrived at - name, vehicle, or not at all
 *   match_score         how sure that was, so a weak match can be shown as weak
 *   fms_driver_id       who the system has on that vehicle, when that differs
 *   status_assumed      true when no status was sent and Present was assumed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_attendance_drafts', function (Blueprint $table) {
            // driver | vehicle - the two registers go to different tables.
            $table->string('kind', 10)->default('driver')->after('line_no')->index();

            $table->string('driver_name_token', 120)->nullable()->after('vehicle_token');

            // name | vehicle | none
            $table->string('matched_by', 10)->nullable()->after('driver_id');
            $table->unsignedTinyInteger('match_score')->nullable()->after('matched_by');

            // Who `drivers.vehicle_id` puts on this vehicle, recorded whenever
            // it is not the man the supervisor named.
            $table->unsignedBigInteger('fms_driver_id')->nullable()->after('match_score');

            $table->string('replacement_name_token', 120)->nullable()->after('fms_driver_id');
            $table->unsignedBigInteger('replacement_driver_id')->nullable()->after('replacement_name_token');
            $table->unsignedBigInteger('replacement_vehicle_id')->nullable()->after('replacement_driver_id');

            $table->string('shift', 20)->nullable()->after('replacement_vehicle_id');
            $table->boolean('status_assumed')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_attendance_drafts', function (Blueprint $table) {
            $table->dropIndex(['kind']);
            $table->dropColumn([
                'kind',
                'driver_name_token',
                'matched_by',
                'match_score',
                'fms_driver_id',
                'replacement_name_token',
                'replacement_driver_id',
                'replacement_vehicle_id',
                'shift',
                'status_assumed',
            ]);
        });
    }
};
