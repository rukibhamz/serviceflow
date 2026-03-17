<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_timers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->cascadeOnDelete();
            $table->string('type');
            $table->timestamp('due_at');
            $table->timestamp('paused_at')->nullable();
            $table->unsignedInteger('paused_minutes')->default(0);
            $table->boolean('breached')->default(false);
            $table->timestamp('stopped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_timers');
    }
};
