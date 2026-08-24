<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->time('field_start_time');
            $table->time('field_end_time');
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->text('summary')->nullable();
            $table->text('challenges')->nullable();
            $table->text('tomorrow_plan')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            $table->timestamps();

            $table->unique(['user_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
