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
        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            // Relasi ke nota peminjaman utama
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
        
            // Relasi ke fisik buku yang dipinjam
            $table->foreignId('book_item_id')->constrained('book_items')->onDelete('restrict');
        
            $table->date('return_date')->nullable();
            $table->integer('renewal_count')->default(0); // Berapa kali diperpanjang
            $table->date('last_renewed_at')->nullable();
        
            $table->integer('late_days')->default(0);
            $table->decimal('fine_amount', 10, 2)->default(0);
        
            $table->enum('return_condition', ['baik', 'rusak ringan', 'rusak berat', 'hilang'])->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan', 'terlambat', 'rusak', 'hilang'])->default('dipinjam');
        
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
