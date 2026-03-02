<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // INV-20260301-XXXX
            $table->foreignId('commission_id')->constrained('commissions')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');

            $table->string('tier'); // basic / standard / premium
            $table->unsignedInteger('price');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('final_price');

            $table->text('description'); // deskripsi request client
            $table->text('notes')->nullable(); // catatan tambahan
            $table->date('client_deadline')->nullable();

            $table->enum('payment_method', [
                'bri', 'seabank', 'bank_jago',
                'dana', 'gopay', 'shopeepay'
            ]);

            $table->enum('status', [
                'pending',          // menunggu konfirmasi artist
                'confirmed',        // artist konfirmasi, in progress
                'waiting_payment',  // artist selesai, menunggu bayar
                'paid',             // client upload bukti bayar
                'completed',        // artist konfirmasi pembayaran
                'cancelled',        // dibatalkan
                'rejected',         // ditolak artist
            ])->default('pending');

            $table->text('cancel_reason')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });

        Schema::create('order_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type');
            $table->string('file_name');
            $table->timestamps();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('proof_path'); // bukti transfer
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_references');
        Schema::dropIfExists('orders');
    }
};