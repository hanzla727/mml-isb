<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Team is the UC-specific instance of a (shared, org-wide) Department
     * — e.g. Fundraising's Donor Relations Team in one UC vs. Fundraising's
     * own team in another UC. The Department itself stays global and is
     * never scoped; the Team is what actually belongs to one UC.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('uc_id')->nullable()->after('department_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            // department_id's foreign key is backed only by the composite
            // unique index being dropped below — give it its own plain index
            // first so MySQL doesn't refuse the drop ("needed in a foreign
            // key constraint").
            $table->index('department_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['department_id', 'name']);
            $table->unique(['uc_id', 'department_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['uc_id', 'department_id', 'name']);
            $table->unique(['department_id', 'name']);
            $table->dropIndex(['department_id']);
            $table->dropConstrainedForeignId('uc_id');
        });
    }
};
