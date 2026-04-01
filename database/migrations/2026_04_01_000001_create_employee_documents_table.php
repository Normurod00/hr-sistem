<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            // File info
            $table->enum('document_type', ['contract', 'diploma', 'certificate', 'id_document', 'medical', 'other'])->default('other');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size')->default(0);

            // AI processing
            $table->longText('parsed_text')->nullable();
            $table->enum('status', ['pending', 'processing', 'parsed', 'failed'])->default('pending');
            $table->json('analysis_result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['employee_profile_id', 'document_type']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
