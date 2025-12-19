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
        Schema::create('application_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_applications_id')
                ->constrained('internship_applications')
                ->cascadeOnDelete();

            $table->foreignId('criterias_id')
                ->constrained('criterias')
                ->cascadeOnDelete();

            $table->decimal('value', 8, 4);
            $table->decimal('normalized_value', 8, 4)->nullable();
            $table->decimal('weighted_value', 8, 4)->nullable();

            $table->timestamps();

            $table->unique([
                'internship_applications_id',
            ]);

            $table->unique([
                'criterias_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_scores');
    }
};
