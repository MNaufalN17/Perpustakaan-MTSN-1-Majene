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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_code')->unique(); // Contoh: TRX-20260508-001
        
            // Relasi ke tabel members (Siapa yang meminjam)
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
        
            $table->date('loan_date');
            $table->date('due_date'); // Tanggal jatuh tempo (+3 hari)
            $table->enum('status', ['aktif', 'selesai', 'terlambat', 'batal'])->default('aktif');
        
            // Relasi ke tabel users (Pustakawan siapa yang melayani)
            $table->foreignId('handled_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
