<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fine_payments', function (Blueprint $table) {
            $table->id();
            // Relasi ke detail peminjaman yang bermasalah (telat/hilang)
            $table->foreignId('loan_item_id')->constrained('loan_items')->onDelete('cascade');
        
            $table->decimal('amount', 10, 2);
            $table->date('payment_date')->nullable();
            $table->enum('payment_status', ['belum dibayar', 'sudah dibayar', 'dibebaskan'])->default('belum dibayar');
        
            // Relasi ke Pustakawan yang menerima uang denda
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
        
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fine_payments');
    }
};
