<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('models', function (Blueprint $table) {
            // Use decimal for deterministic precision; nullable so existing rows aren't affected.
            $table->decimal('window_context', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn('window_context');
        });
    }
};
