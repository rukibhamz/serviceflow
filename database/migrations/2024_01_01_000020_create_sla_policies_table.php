<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('priority');
            $table->string('ticket_type')->nullable();
            $table->unsignedInteger('response_minutes');
            $table->unsignedInteger('resolution_minutes');
            $table->json('business_hours')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
