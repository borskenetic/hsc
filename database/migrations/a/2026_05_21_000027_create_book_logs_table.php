<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('patron_name')->nullable();
            $table->string('status');
            $table->string('circulation_type', 20);
            $table->unsignedTinyInteger('renew_count');
            $table->dateTime('last_renewed_at')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('returned_date')->nullable();
            $table->decimal('fine_incurred', 8, 2)->nullable();
            $table->decimal('fine_original', 8, 2)->nullable();
            $table->decimal('fine_balance', 8, 2)->nullable();
            $table->decimal('fine_paid_total', 8, 2)->nullable();
            $table->decimal('fine_waived_total', 8, 2)->nullable();
            $table->timestamp('fine_cleared_at')->nullable();
            $table->string('fine_clearance_type', 32)->nullable();
            $table->text('fine_clearance_note')->nullable();
            $table->unsignedBigInteger('fine_cleared_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_logs');
    }
};
