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
            // 1. Hapus Foreign Key terlebih dahulu agar Index-nya bisa dilepas
            // Gunakan array syntax agar Laravel otomatis mencari nama FK-nya
            $table->dropForeign(['criterias_id']);

            // 2. Sekarang Index Unik sudah bebas, hapus index yang salah
            $table->dropUnique('application_scores_criterias_id_unique');

            // 3. Pasang kembali Foreign Key-nya
            // (MySQL otomatis akan membuat index standar non-unique untuk ini)
            $table->foreign('criterias_id')
                  ->references('id')
                  ->on('criterias') // Pastikan nama tabel referensinya 'criterias'
                  ->onDelete('cascade');

            // 4. Tambahkan Index Unik Kombinasi (Solusi Utama)
            // Artinya: Kombinasi (App ID + Criteria ID) tidak boleh kembar
            $table->unique(['internship_applications_id', 'criterias_id'], 'score_app_criteria_unique');
        });
    }

    public function down(): void
    {
        Schema::table('application_scores', function (Blueprint $table) {
            // Rollback logic (Urutan dibalik)
            $table->dropUnique('score_app_criteria_unique');
            $table->dropForeign(['criterias_id']);
            
            // Kembalikan ke error lama (Unique hanya di criterias_id)
            $table->unique('criterias_id', 'application_scores_criterias_id_unique');
            
            $table->foreign('criterias_id')
                  ->references('id')
                  ->on('criterias')
                  ->onDelete('cascade');
        });
    }
};
