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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id');
            $table->string('tenant_domain');
            $table->string('product');
            $table->string('policy');
            $table->string('status')->default('ACTIVE');
            $table->timestamps();
            $table->unique(['account_id', 'policy']);
            $table->index(['account_id', 'policy', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
