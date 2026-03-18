<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng users mới
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('roles', ['admin', 'customer'])->default('customer');
            $table->string('avatar')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->default(1);
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Xoá bảng khi rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
