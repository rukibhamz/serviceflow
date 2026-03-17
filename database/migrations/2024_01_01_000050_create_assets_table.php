<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('asset_tag')->unique()->nullable();
            $table->string('type');
            $table->string('serial_number')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->date('purchased_at')->nullable();
            $table->date('eol_at')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
