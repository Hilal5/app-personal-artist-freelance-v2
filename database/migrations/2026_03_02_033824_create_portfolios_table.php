<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('software')->nullable();
            $table->string('client_name')->nullable();
            $table->date('created_date')->nullable();
            $table->enum('status', ['published', 'draft'])->default('published');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });

        Schema::create('portfolio_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type'); // image / video
            $table->boolean('is_cover')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('portfolio_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('tag');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_tags');
        Schema::dropIfExists('portfolio_media');
        Schema::dropIfExists('portfolios');
    }
};