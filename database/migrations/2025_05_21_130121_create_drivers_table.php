<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('serial_no')->unique();
            $table->string('full_name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('designation')->nullable();
            $table->string('status')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('vehicle_list')->nullable();
            $table->string('account_no')->nullable();
            $table->date('employment_date')->nullable();
            $table->boolean('employment_form')->nullable();
            $table->boolean('cnic_copy')->nullable();
            $table->string('license_copy')->nullable();
            $table->string('ddc')->nullable();
            $table->string('driver_form')->nullable();
            $table->string('employee_card')->nullable();
            $table->string('medical_report')->nullable();
            $table->date('eobi_start')->nullable();
            $table->string('eobi_no')->nullable();
            $table->date('dob')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('cnic_no')->nullable();
            $table->date('cnic_issue')->nullable();
            $table->date('cnic_expiry')->nullable();
            $table->string('license_no')->nullable();
            $table->string('license_category')->nullable();
            $table->date('license_issue')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('blacklist')->nullable();
            $table->string('blacklist_reason')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('authority_letter')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
}
