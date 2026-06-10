<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('visitor_name', 150);
            $table->string('identity_number', 50)->nullable();
            $table->string('visitor_type', 30)->default('siswa');
            $table->foreignId('student_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('visit_purpose', 100);
            $table->date('visit_date');
            $table->time('check_in_time')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['visit_date', 'visitor_type']);
            $table->index('visitor_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_visits');
    }
};
