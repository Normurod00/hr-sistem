<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица для обмена SDP и ICE кандидатами
        Schema::create('webrtc_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('video_meetings')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('type', ['offer', 'answer', 'ice-candidate', 'renegotiate']);
            $table->json('data');
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->index(['meeting_id', 'recipient_id', 'processed']);
            $table->index(['meeting_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webrtc_signals');
    }
};
