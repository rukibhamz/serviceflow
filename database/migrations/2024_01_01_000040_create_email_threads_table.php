<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('message_id')->unique();
            $table->string('in_reply_to')->nullable();
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->string('direction');
            $table->text('raw_headers')->nullable();
            $table->timestamps();

            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_threads');
    }
};
