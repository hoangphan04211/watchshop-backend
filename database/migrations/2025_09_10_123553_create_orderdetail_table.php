<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('qty');
            $table->json('attributes')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('discount', 15, 2);
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_details');
    }
};
