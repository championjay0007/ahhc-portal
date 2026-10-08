<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table): void {
            $table->text('message')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('enquiries')->whereNull('message')->update(['message' => '']);

        Schema::table('enquiries', function (Blueprint $table): void {
            $table->text('message')->nullable(false)->change();
        });
    }
};