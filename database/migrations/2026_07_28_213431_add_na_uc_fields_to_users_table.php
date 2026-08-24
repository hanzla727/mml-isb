<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // An NA Head's own NA (their whole managed unit), also
            // denormalized onto every regular volunteer/team leader (mirrors
            // department_id/team_id already being denormalized alongside
            // team membership) so NA-level scoping queries never need to
            // join through uc -> na.
            $table->foreignId('na_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            // A volunteer/team leader's operating UC. Null for an NA Head,
            // who oversees every UC under their na_id rather than one.
            $table->foreignId('uc_id')->nullable()->after('na_id')->constrained()->nullOnDelete();
            // Generalized "who this person answers to" pointer — for a
            // Volunteer this is normally their Team Leader, for a Team
            // Leader it's their NA Head, etc. Explicit rather than derived
            // so it survives a volunteer moving between teams.
            $table->foreignId('reporting_head_id')->nullable()->after('uc_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reporting_head_id');
            $table->dropConstrainedForeignId('uc_id');
            $table->dropConstrainedForeignId('na_id');
        });
    }
};
