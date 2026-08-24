<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('category', ['meeting_reminder', 'event', 'deadline', 'general'])->default('general');
            $table->enum('audience_scope', ['all', 'na', 'uc', 'department', 'team', 'user'])->default('all');
            $table->unsignedBigInteger('audience_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['audience_scope', 'audience_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
