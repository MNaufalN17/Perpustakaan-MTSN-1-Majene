<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (! Schema::hasColumn('loans', 'loan_type')) {
                $table->string('loan_type', 30)
                    ->default('regular')
                    ->after('status')
                    ->index();
            }

            if (! Schema::hasColumn('loans', 'student_class_id')) {
                $table->unsignedBigInteger('student_class_id')
                    ->nullable()
                    ->after('member_id')
                    ->index();
            }

            if (! Schema::hasColumn('loans', 'notes')) {
                $table->text('notes')
                    ->nullable()
                    ->after('loan_type');
            }

            if (! Schema::hasColumn('loans', 'return_notes')) {
                $table->text('return_notes')
                    ->nullable()
                    ->after('notes');
            }

            if (! Schema::hasColumn('loans', 'return_date')) {
                $table->date('return_date')
                    ->nullable()
                    ->after('due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'return_date')) {
                $table->dropColumn('return_date');
            }

            if (Schema::hasColumn('loans', 'return_notes')) {
                $table->dropColumn('return_notes');
            }

            if (Schema::hasColumn('loans', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('loans', 'student_class_id')) {
                $table->dropColumn('student_class_id');
            }

            if (Schema::hasColumn('loans', 'loan_type')) {
                $table->dropColumn('loan_type');
            }
        });
    }
};