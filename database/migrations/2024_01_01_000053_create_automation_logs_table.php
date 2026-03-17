<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('automation_id')->constrained('automations')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->json('conditions_snapshot');
            $table->json('actions_executed');
            $table->string('result');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
