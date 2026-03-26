<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем новые поля в video_meetings
        Schema::table('video_meetings', function (Blueprint $table) {
            $table->string('room_id')->nullable()->unique()->after('meeting_link');
            $table->timestamp('started_at')->nullable()->after('scheduled_at');
            $table->timestamp('ended_at')->nullable()->after('started_at');
            $table->integer('max_participants')->default(10)->after('duration_minutes');
            $table->json('settings')->nullable()->after('notes');
        });

        // Таблица участников встречи
        Schema::create('video_meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('video_meetings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['host', 'moderator', 'participant'])->default('participant');
            $table->enum('status', ['invited', 'accepted', 'declined', 'joined', 'left'])->default('invited');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_video_off')->default(false);
            $table->timestamps();

            $table->unique(['meeting_id', 'user_id']);
            $table->index('user_id');
            $table->index(['meeting_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_meeting_participants');

        Schema::table('video_meetings', function (Blueprint $table) {
            $table->dropColumn(['room_id', 'started_at', 'ended_at', 'max_participants', 'settings']);
        });
    }
};
