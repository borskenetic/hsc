<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_marc_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string('tag', 3);
            $table->string('subfield', 1)->nullable();
            $table->string('indicator1', 1)->nullable();
            $table->string('indicator2', 1)->nullable();
            $table->unsignedInteger('occurrence');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_marc_fields');
    }
};
