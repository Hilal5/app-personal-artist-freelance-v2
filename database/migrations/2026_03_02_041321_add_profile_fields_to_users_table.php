<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('password');
            $table->string('username')->nullable()->unique()->after('avatar');
            $table->text('bio')->nullable()->after('username');
            $table->string('location')->nullable()->after('bio');
            $table->string('language')->nullable()->after('location');
            $table->string('instagram')->nullable()->after('language');
            $table->string('twitter')->nullable()->after('instagram');
            $table->string('tiktok')->nullable()->after('twitter');
            $table->string('website')->nullable()->after('tiktok');
            $table->enum('commission_status', ['open', 'closed'])->default('open')->after('website');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'username', 'bio', 'location', 'language',
                'instagram', 'twitter', 'tiktok', 'website', 'commission_status'
            ]);
        });
    }
};