<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        if (!Schema::hasColumn('services', 'image')) {
            Schema::table('services', function (Blueprint $table) {
                // add nullable image after price if possible
                $table->string('image')->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('services') && Schema::hasColumn('services', 'image')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
