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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->index();
            $table->bigInteger('policy_id');
            $table->bigInteger('open_policy_run_id');
            $table->bigInteger('close_policy_run_id')->nullable();
            $table->string('status')->default('OPEN');
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->index(['account_id', 'policy_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
