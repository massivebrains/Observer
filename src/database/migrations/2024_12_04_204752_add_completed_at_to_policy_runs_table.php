<?php declare(strict_types=1);

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
        Schema::table('policy_runs', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable()->after('subjects_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_runs', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
