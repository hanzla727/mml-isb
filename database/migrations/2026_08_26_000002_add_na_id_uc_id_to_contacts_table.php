<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('na_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
            $table->foreignId('uc_id')->nullable()->after('na_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('na_id');
            $table->dropConstrainedForeignId('uc_id');
        });
    }
};
