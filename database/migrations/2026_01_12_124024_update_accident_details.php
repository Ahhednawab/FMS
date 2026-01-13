<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accident_details', function (Blueprint $table) {

            $table->string('vehicle_no')->nullable()->after('accident_id');
            $table->string('insurance')->nullable()->after('vehicle_no');
            $table->string('ownership')->nullable()->after('insurance');

            $table->string('driver_name')->nullable()->after('ownership');
            $table->string('license_no')->nullable()->after('driver_name');
            $table->string('policy_no')->nullable()->after('license_no');

            $table->string('workshop')->nullable()->after('policy_no');
            $table->longText('third_party')->nullable()->after('workshop');

            // Monetary fields (unsigned integers)
            $table->unsignedInteger('claim_amount')->default(0)->after('third_party');
            $table->unsignedInteger('depreciation_amount')->default(0)->after('claim_amount');

            $table->longText('remarks')->nullable()->after('depreciation_amount');

            $table->boolean('bill_to_ke')->default(false)->after('remarks');

            $table->string('payment_status')->default('pending')->after('bill_to_ke');


        });

        Schema::table('accident_details', function (Blueprint $table) {
            $table->dropColumn([

                'accident_type',
                'location',
                'accident_date',
                'accident_time',
                'accident_description',
                'person_involved',
                'injury_type',
                'damage_type',
                'order_quantity',
                'order_price',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accident_details', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_no',
                'insurance',
                'ownership',
                'driver_name',
                'license_no',
                'policy_no',
                'workshop',
                'third_party',
                'claim_amount',
                'depreciation_amount',
                'remarks',
                'bill_to_ke',
                'payment_status',
            ]);
        });
    }
};
