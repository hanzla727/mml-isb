<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NA (National Assembly constituency) is the unit a person is actually
     * assigned to manage (its "NA Head"). Islamabad Capital Territory has no
     * Provincial Assembly (PP) — it's a federal territory, not part of any
     * province — so NA is the top real level, with UC directly underneath.
     */
    public function up(): void
    {
        Schema::create('nas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            // Nullable + set after users exist: an NA is created before its
            // head is assigned, and the FK to users can't be satisfied yet.
            $table->foreignId('na_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nas');
    }
};
