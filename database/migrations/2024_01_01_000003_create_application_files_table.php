<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('file_type', ['resume', 'id_document', 'certificate', 'portfolio', 'other'])->default('resume');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->longText('parsed_text')->nullable();
            $table->boolean('is_parsed')->default(false);
            $table->timestamps();

            $table->index('file_type');
            $table->index('is_parsed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_files');
    }
};
