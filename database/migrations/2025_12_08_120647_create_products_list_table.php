<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_list', function (Blueprint $table) {
            $table->id(); // id (bigint unsigned auto_increment primary key)

            $table->string('serial_no')->unique(); // e.g. SKU, PROD-001
            $table->string('name');

            $table->foreignId('product_category_id')
                ->constrained('product_categories') // assumes table name is product_categories
                ->onDelete('restrict');

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->onDelete('restrict');

            $table->foreignId('unit_id')
                ->constrained('units') // Laravel will assume "units" table
                ->onDelete('restrict');

            $table->foreignId('vendor_id')
                ->nullable() // if a product can exist without vendor
                ->constrained('vendors')
                ->onDelete('set null');

            $table->boolean('is_active')->default(true); // is_active

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_list');
    }
};
