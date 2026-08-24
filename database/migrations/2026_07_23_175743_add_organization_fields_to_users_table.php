<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->foreignId('department_id')->nullable()->after('avatar_path')->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('team_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('team_id');
            $table->dropColumn(['phone', 'avatar_path', 'is_active', 'last_login_at']);
        });
    }
};
