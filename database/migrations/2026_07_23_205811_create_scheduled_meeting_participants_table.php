<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['scheduled_meeting_id', 'user_id'], 'sched_meeting_participants_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_meeting_participants');
    }
};
