<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Project" (Hospital Project, Mosque Construction, Fundraising
     * Campaign, ...) is the same concept the Campaign module already
     * modeled — just rescoped to live under a Department instead of
     * floating at the organization level. Renaming in place rather than
     * building a second, near-identical module.
     */
    public function up(): void
    {
        Schema::rename('campaigns', 'projects');

        Schema::table('scheduled_meetings', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->renameColumn('campaign_id', 'project_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->renameColumn('campaign_id', 'project_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('department_id')->after('id')->constrained()->cascadeOnDelete();
            // A Project is UC-specific work even though its Department is
            // shared/global across every UC (e.g. a Fundraising project
            // running specifically in one UC, not every UC with a
            // Fundraising department).
            $table->foreignId('uc_id')->after('department_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('scheduled_meetings', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->renameColumn('project_id', 'campaign_id');
        });

        Schema::table('scheduled_meetings', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->renameColumn('project_id', 'campaign_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uc_id');
            $table->dropConstrainedForeignId('department_id');
        });

        Schema::rename('projects', 'campaigns');

        Schema::table('scheduled_meetings', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
        });
    }
};
