<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_sales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('product_id');
            $table->decimal('price_sale', 15, 2);
            $table->dateTime('date_begin');
            $table->dateTime('date_end');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedInteger('created_by')->default(1);
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_sales');
    }
};
