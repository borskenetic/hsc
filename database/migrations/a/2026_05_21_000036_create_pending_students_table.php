<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_students', function (Blueprint $table) {
            $table->id();
            $table->string('id_number')->nullable();
            $table->string('lastname');
            $table->string('firstname');
            $table->string('middle_initial')->nullable();
            $table->date('birthday')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('course')->nullable();
            $table->string('year')->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_person')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_number', 20)->nullable();
            $table->text('emergency_address')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('student_signature')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_students');
    }
};
