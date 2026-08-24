<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UC (Union Council) is the bottom-most operational unit — this is
     * where Teams, Projects, and Volunteers actually attach. "Sector" (e.g.
     * F-10, G-9) is kept here as a purely optional, informal label, not a
     * structural level of its own.
     */
    public function up(): void
    {
        Schema::create('ucs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('na_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sector')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ucs');
    }
};
