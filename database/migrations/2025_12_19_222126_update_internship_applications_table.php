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
        Schema::table('internship_applications', function (Blueprint $table) {
            if(Schema::hasColumn('internship_applications', 'profiles_id')) {
                $table->dropForeign(['profiles_id']);
                $table->dropColumn('profiles_id');
            }

            $table->foreignId('users_id')
                ->after('programs_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status')->default('pending')->change();

            $table->decimal('final_score', 8, 4)->nullable()->change();
            $table->integer('rank')->nullable()->change();

            $table->decimal('final_score', 8, 4)->nullable()->change();
            $table->integer('rank')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('internship_applications', function (Blueprint $table) {
            $table->dropForeign(['users_id']);
            $table->dropColumn('users_id');

            $table->foreignId('profiles_id')->nullable();
            $table->renameColumn('program_id', 'programs_id');
        });
    }
};
