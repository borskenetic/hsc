<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_year_id');
            $table->string('course_code', 20);
            $table->string('course_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_courses');
    }
};
