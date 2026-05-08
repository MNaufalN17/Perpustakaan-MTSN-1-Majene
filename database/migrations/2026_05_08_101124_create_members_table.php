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
        Schema::create('members', function (Blueprint $table) {
        $table->id();
        $table->string('member_code')->unique();
        $table->string('nis_nip')->unique();
        $table->string('name');
        
        $table->enum('member_type', ['siswa', 'guru']);
        $table->enum('gender', ['laki-laki', 'perempuan']);
        
        $table->foreignId('student_class_id')->nullable()->constrained('classes')->onDelete('set null');
        
        $table->string('phone')->nullable();
        $table->string('card_image')->nullable();
        $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
