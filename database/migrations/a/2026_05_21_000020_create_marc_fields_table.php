<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marc_fields', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 3);
            $table->string('subfield', 1)->nullable();
            $table->string('label')->nullable();
            $table->boolean('repeatable');
            $table->string('input_type');
            $table->longText('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marc_fields');
    }
};
