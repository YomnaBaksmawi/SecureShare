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
      Schema::create('document_shares', function (Blueprint $table) {
    $table->id();

    $table->foreignId('document_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('permission');
    $table->timestamp('expires_at')->nullable();
    $table->timestamp('revoked_at')->nullable();

    $table->timestamps();

    $table->index('user_id');
    $table->index('document_id');
    $table->index('expires_at');
});
DB::statement('
    CREATE UNIQUE INDEX document_shares_active_unique
    ON document_shares(document_id, user_id)
    WHERE revoked_at IS NULL
');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_shares');
        DB::statement('
    DROP INDEX IF EXISTS document_shares_active_unique
');
    }
};
