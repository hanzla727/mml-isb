<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The Team concept is removed entirely — Department stays as the shared,
 * org-wide category; there's no more UC-specific "instance" of it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('teams');
    }

    public function down(): void
    {
        // Not reversible — the Team model/migrations that created this
        // table have been removed alongside it.
    }
};
