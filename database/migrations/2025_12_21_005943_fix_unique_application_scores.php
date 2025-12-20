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
        Schema::table('application_scores', function (Blueprint $table) {
            // 1️⃣ DROP FOREIGN KEY dulu
            $table->dropForeign(['internship_applications_id']);

            // 2️⃣ DROP UNIQUE lama
            $table->dropUnique('application_scores_internship_applications_id_unique');

            // 3️⃣ TAMBAH composite unique
            $table->unique(
                ['internship_applications_id', 'criterias_id'],
                'application_scores_unique_application_criteria'
            );

            // 4️⃣ BALIKKAN FOREIGN KEY
            $table->foreign('internship_applications_id')
                  ->references('id')
                  ->on('internship_applications')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_scores', function (Blueprint $table) {
            $table->dropForeign(['internship_applications_id']);
            $table->dropUnique('application_scores_unique_application_criteria');

            $table->unique('internship_applications_id');

            $table->foreign('internship_applications_id')
                  ->references('id')
                  ->on('internship_applications')
                  ->cascadeOnDelete();
        });
    }
};
