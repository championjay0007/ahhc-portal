<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->string('abn_number')->nullable()->after('notes');
        });

        // Backfill existing ABN values from notes where present (notes may contain 'ABN: 123...')
        $workers = DB::table('workers')->select('id', 'notes')->get();
        foreach ($workers as $w) {
            if (empty($w->notes)) {
                continue;
            }

            if (preg_match('/ABN:\s*([0-9A-Za-z\-]+)/i', $w->notes, $m)) {
                DB::table('workers')->where('id', $w->id)->update(['abn_number' => $m[1]]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn('abn_number');
        });
    }
};
