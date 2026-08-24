<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_meeting_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->enum('status', [
                'not_assigned', 'assigned', 'accepted', 'in_progress', 'waiting_for_information',
                'completed', 'report_submitted', 'under_review', 'approved', 'rejected',
                'needs_revision', 're_submitted', 'closed', 'cancelled',
            ])->default('not_assigned');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
