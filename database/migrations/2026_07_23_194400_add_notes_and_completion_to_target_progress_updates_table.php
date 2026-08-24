<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('target_progress_updates', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('current_value');
            $table->boolean('is_completed')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('target_progress_updates', function (Blueprint $table) {
            $table->dropColumn(['notes', 'is_completed']);
        });
    }
};
