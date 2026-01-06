<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'public_key')) {
                $table->uuid('public_key')->nullable()->unique()->after('remember_token');
            }

            if (! Schema::hasColumn('users', 'public_slug')) {
                $table->string('public_slug')->nullable()->unique()->after('public_key');
            }
        });

        DB::table('users')
            ->whereNull('public_key')
            ->orderBy('id')
            ->lazyById()
            ->each(function ($user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['public_key' => (string) Str::uuid()]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['public_key']);
            $table->dropUnique(['public_slug']);
            $table->dropColumn(['public_key', 'public_slug']);
        });
    }
};
