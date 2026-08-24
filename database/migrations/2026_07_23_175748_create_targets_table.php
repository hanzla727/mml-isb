<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->enum('metric', ['hours', 'meetings', 'custom']);
            $table->decimal('target_value', 10, 2);
            $table->enum('scope', ['all', 'na', 'uc', 'department', 'team', 'user']);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
