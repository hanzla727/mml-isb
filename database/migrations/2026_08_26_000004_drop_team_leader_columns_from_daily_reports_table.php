<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily report review is now a single stage (whoever holds review-reports
 * and can see the volunteer — their UC Head, NA Head, or Admin — reviews
 * directly), so the separate team-leader review stage's columns go away.
 * admin_reviewed_by/admin_reviewed_at/admin_remarks now record that one
 * review, regardless of which of those roles the reviewer holds.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_leader_id');
            $table->dropConstrainedForeignId('team_leader_reviewed_by');
            $table->dropColumn(['team_leader_reviewed_at', 'team_leader_remarks']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->foreignId('team_leader_id')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->foreignId('team_leader_reviewed_by')->nullable()->after('team_leader_id')->constrained('users')->nullOnDelete();
            $table->timestamp('team_leader_reviewed_at')->nullable()->after('team_leader_reviewed_by');
            $table->text('team_leader_remarks')->nullable()->after('team_leader_reviewed_at');
        });
    }
};
