<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csat_surveys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'requester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csat_surveys');
    }
};
