<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->integer('estimated_days');
            $table->integer('max_slots');
            $table->integer('used_slots')->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');

            // Tier Basic
            $table->string('tier_basic_name')->default('Basic');
            $table->text('tier_basic_desc')->nullable();
            $table->unsignedInteger('tier_basic_price');
            $table->unsignedInteger('tier_basic_discount')->default(0); // persen

            // Tier Standard
            $table->string('tier_standard_name')->default('Standard');
            $table->text('tier_standard_desc')->nullable();
            $table->unsignedInteger('tier_standard_price');
            $table->unsignedInteger('tier_standard_discount')->default(0);

            // Tier Premium
            $table->string('tier_premium_name')->default('Premium');
            $table->text('tier_premium_desc')->nullable();
            $table->unsignedInteger('tier_premium_price');
            $table->unsignedInteger('tier_premium_discount')->default(0);

            $table->timestamps();
        });

        Schema::create('commission_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('commissions')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type'); // image / video
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_media');
        Schema::dropIfExists('commissions');
    }
};