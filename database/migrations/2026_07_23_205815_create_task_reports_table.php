<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('work_summary')->nullable();
            $table->text('description')->nullable();
            $table->text('achievements')->nullable();
            $table->text('problems_faced')->nullable();
            $table->text('next_plan')->nullable();
            $table->decimal('working_hours', 6, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('review_status', [
                'pending', 'under_review', 'approved', 'approved_with_remarks',
                'rejected', 'needs_revision', 're_submitted', 'closed',
            ])->default('pending');
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reports');
    }
};
