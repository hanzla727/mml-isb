<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('general');
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('agenda')->nullable();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_meetings');
    }
};
