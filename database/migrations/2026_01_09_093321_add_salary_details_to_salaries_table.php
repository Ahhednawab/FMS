<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('paid_absent')->default(0)->after('advance_deduction');
            $table->decimal('extra', 10, 2)->default(0)->after('paid_absent');

            $table->decimal('total_recovered', 10, 2)->default(0)->after('extra');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('total_recovered');

            $table->enum('status', ['pending', 'paid'])
                ->default('pending')
                ->after('remaining_amount');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'paid_absent',
                'extra',
                'total_recovered',
                'remaining_amount',
                'status',
            ]);
        });
    }
};
