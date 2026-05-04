<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        $connection = config('radar.storage.database.connection');

        return is_string($connection) ? $connection : null;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->getConnection())->create('radar_scans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedInteger('vulnerability_count')->default(0);
            $table->unsignedInteger('package_count')->default(0);
            $table->json('payload');
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('radar_scans');
    }
};
