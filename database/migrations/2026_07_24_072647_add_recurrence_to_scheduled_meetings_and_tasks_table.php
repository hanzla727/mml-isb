<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_meetings', function (Blueprint $table) {
            $table->json('recurrence_rule')->nullable()->after('campaign_id');
            $table->foreignId('recurring_parent_id')->nullable()->after('recurrence_rule')
                ->constrained('scheduled_meetings')->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->json('recurrence_rule')->nullable()->after('campaign_id');
            $table->foreignId('recurring_parent_id')->nullable()->after('recurrence_rule')
                ->constrained('tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurring_parent_id');
            $table->dropColumn('recurrence_rule');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurring_parent_id');
            $table->dropColumn('recurrence_rule');
        });
    }
};
