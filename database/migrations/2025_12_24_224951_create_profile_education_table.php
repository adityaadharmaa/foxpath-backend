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
        Schema::create('profile_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profiles_id')->constrained()->cascadeOnDelete();

            $table->enum('level', ['siswa', 'mahasiswa']);
            $table->string('institution_name');
            $table->string('major')->nullable();

            $table->decimal('gpa', 3,2)->nullable();
            $table->decimal('average_score', 5,2)->nullable();

            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_education');
    }
};
