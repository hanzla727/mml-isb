<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A UC Head (unlike a plain volunteer's single uc_id) can be assigned
     * several UCs, so this is a many-to-many pivot — mirrors admin_na.
     */
    public function up(): void
    {
        Schema::create('uc_heads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uc_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'uc_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uc_heads');
    }
};
