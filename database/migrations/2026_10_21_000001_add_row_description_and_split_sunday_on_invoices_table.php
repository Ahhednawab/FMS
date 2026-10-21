<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Har vehicle row ki apni description (baqi row fields ki tarah array).
            if (! Schema::hasColumn('invoices', 'vehicle_description')) {
                $table->longText('vehicle_description')->nullable()->after('po_no');
            }

            // "Sunday Gazette" ab teen alag amounts.
            if (! Schema::hasColumn('invoices', 'sunday')) {
                $table->decimal('sunday', 15, 2)->nullable()->after('monthly_rent');
            }

            if (! Schema::hasColumn('invoices', 'gazetted_12hr')) {
                $table->decimal('gazetted_12hr', 15, 2)->nullable()->after('sunday');
            }

            if (! Schema::hasColumn('invoices', 'gazetted_24hr')) {
                $table->decimal('gazetted_24hr', 15, 2)->nullable()->after('gazetted_12hr');
            }
        });

        // Purani combined amount "Sunday" mein chali jati hai, taake purani
        // invoices ka Total Claim bilkul wahi rahe jo pehle tha.
        DB::table('invoices')
            ->whereNull('sunday')
            ->update(['sunday' => DB::raw('COALESCE(sunday_gazette, 0)')]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            foreach (['vehicle_description', 'sunday', 'gazetted_12hr', 'gazetted_24hr'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
