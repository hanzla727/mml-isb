<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_progress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period_key');
            $table->decimal('current_value', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['target_id', 'user_id', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_progress_updates');
    }
};
