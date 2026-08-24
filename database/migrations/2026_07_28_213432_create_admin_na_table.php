<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An Admin (unlike an NA Head) can be assigned several NAs, so this is a
     * many-to-many pivot rather than a single na_id column.
     */
    public function up(): void
    {
        Schema::create('admin_na', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('na_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'na_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_na');
    }
};
