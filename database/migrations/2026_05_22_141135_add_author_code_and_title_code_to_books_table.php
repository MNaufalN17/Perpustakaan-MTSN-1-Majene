<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('books', 'author_code')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('author_code', 50)->nullable()->after('author');
            });
        }

        if (!Schema::hasColumn('books', 'title_code')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('title_code', 50)->nullable()->after('author_code');
            });
        }

        DB::table('books')->orderBy('id')->get()->each(function ($book) {
            $authorLetters = preg_replace('/[^a-zA-Z]/', '', $book->author ?? '');
            $titleLetters = preg_replace('/[^a-zA-Z0-9]/', '', $book->title ?? '');

            DB::table('books')
                ->where('id', $book->id)
                ->update([
                    'author_code' => $book->author_code ?: ($authorLetters ? ucfirst(strtolower(substr($authorLetters, 0, 3))) : 'Pen'),
                    'title_code' => $book->title_code ?: ($titleLetters ? strtolower(substr($titleLetters, 0, 1)) : 'b'),
                ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('books', 'title_code')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('title_code');
            });
        }

        if (Schema::hasColumn('books', 'author_code')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('author_code');
            });
        }
    }
};