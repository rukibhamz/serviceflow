<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('body');
            $table->string('status')->default('draft');
            $table->foreignId('category_id')->nullable()->constrained('article_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('helpful_votes')->default(0);
            $table->unsignedInteger('unhelpful_votes')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
