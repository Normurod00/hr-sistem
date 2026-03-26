<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50); // hr, finance, security, operations, it
            $table->string('code', 50)->unique(); // POL-HR-001
            $table->string('title', 255);
            $table->text('summary')->nullable();
            $table->longText('content'); // полный текст или markdown
            $table->string('file_path', 500)->nullable(); // путь к PDF
            $table->boolean('is_active')->default(true);
            $table->string('version', 20)->default('1.0');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tags')->nullable(); // ["leave", "vacation", "sick"]
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
            $table->index('effective_date');
            $table->fullText(['title', 'content']); // для поиска (MySQL/PostgreSQL)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
