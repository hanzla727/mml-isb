<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->enum('review_status', [
                'pending_review', 'under_review', 'approved', 'approved_with_remarks',
                'needs_revision', 're_submitted', 'rejected', 'closed',
            ])->nullable()->after('status');
            $table->foreignId('team_leader_id')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->foreignId('team_leader_reviewed_by')->nullable()->after('team_leader_id')->constrained('users')->nullOnDelete();
            $table->timestamp('team_leader_reviewed_at')->nullable()->after('team_leader_reviewed_by');
            $table->text('team_leader_remarks')->nullable()->after('team_leader_reviewed_at');
            $table->foreignId('admin_reviewed_by')->nullable()->after('team_leader_remarks')->constrained('users')->nullOnDelete();
            $table->timestamp('admin_reviewed_at')->nullable()->after('admin_reviewed_by');
            $table->text('admin_remarks')->nullable()->after('admin_reviewed_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_leader_id');
            $table->dropConstrainedForeignId('team_leader_reviewed_by');
            $table->dropConstrainedForeignId('admin_reviewed_by');
            $table->dropColumn([
                'review_status', 'team_leader_reviewed_at', 'team_leader_remarks',
                'admin_reviewed_at', 'admin_remarks', 'deleted_at',
            ]);
        });
    }
};
