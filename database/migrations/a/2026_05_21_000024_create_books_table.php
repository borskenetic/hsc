<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('control_no')->nullable();
            $table->string('date_time_stamp')->nullable();
            $table->string('fixed_length_data')->nullable();
            $table->string('isbn')->nullable();
            $table->string('price')->nullable();
            $table->string('cataloging_source_a')->nullable();
            $table->string('cataloging_source_b')->nullable();
            $table->string('cataloging_source_e')->nullable();
            $table->string('main_author')->nullable();
            $table->string('title_statement')->nullable();
            $table->string('title_author')->nullable();
            $table->string('edition')->nullable();
            $table->string('pub_place')->nullable();
            $table->string('publisher')->nullable();
            $table->string('pub_year')->nullable();
            $table->string('pages')->nullable();
            $table->string('illustrations')->nullable();
            $table->string('size')->nullable();
            $table->string('volume')->nullable();
            $table->string('content_type')->nullable();
            $table->string('content_code')->nullable();
            $table->string('media_type')->nullable();
            $table->string('media_code')->nullable();
            $table->string('carrier_type')->nullable();
            $table->string('carrier_code')->nullable();
            $table->string('series_title')->nullable();
            $table->text('general_note')->nullable();
            $table->text('bibliography_note')->nullable();
            $table->string('source_vendor')->nullable();
            $table->date('source_date')->nullable();
            $table->string('subject_topic')->nullable();
            $table->string('subject_form')->nullable();
            $table->string('genre')->nullable();
            $table->string('library_name')->nullable();
            $table->string('section')->nullable();
            $table->string('call_number')->nullable();
            $table->string('accession_no')->nullable();
            $table->string('barcode')->nullable();
            $table->string('rfid')->nullable();
            $table->string('availability');
            $table->string('year')->nullable();
            $table->string('course')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
