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
        Schema::create('book_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
        
            $table->string('item_code')->unique(); // Contoh: 004-MAR-i-001
            $table->string('classification_code'); // Contoh: 004
            $table->string('author_code');         // Contoh: MAR
            $table->string('title_initial');       // Contoh: i
            $table->integer('copy_number');        // Contoh: 1
        
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak', 'hilang', 'nonaktif'])->default('tersedia');
            $table->enum('condition', ['baik', 'rusak ringan', 'rusak berat', 'hilang'])->default('baik');
        
            $table->string('location')->nullable(); // Lokasi rak
            $table->date('acquisition_date')->nullable(); // Tanggal masuk
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_items');
    }
};
