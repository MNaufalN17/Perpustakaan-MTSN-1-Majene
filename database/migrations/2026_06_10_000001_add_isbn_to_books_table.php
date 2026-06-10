<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('books', 'isbn')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('isbn', 30)
                    ->nullable()
                    ->unique('books_isbn_unique')
                    ->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('books', 'isbn')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropUnique('books_isbn_unique');
                $table->dropColumn('isbn');
            });
        }
    }
};
