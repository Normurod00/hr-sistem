<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Флаг: это сотрудник банка (не кандидат)
            $table->boolean('is_employee')->default(false)->after('role');
            // Предпочтения уведомлений
            $table->json('notification_preferences')->nullable()->after('is_employee');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_employee', 'notification_preferences']);
        });
    }
};
