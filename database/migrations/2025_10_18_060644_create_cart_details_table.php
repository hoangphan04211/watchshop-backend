<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product')->onDelete('cascade');
            $table->json('attributes')->nullable(); // lưu các thuộc tính như màu sắc, size
            $table->integer('qty')->default(1); // số lượng
            $table->decimal('price', 15, 2);    // giá gốc
            $table->decimal('price_sale', 15, 2)->nullable(); // giá sale nếu có
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_details');
    }
};
