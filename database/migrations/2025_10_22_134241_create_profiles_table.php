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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id');
            $table->enum("applicant_type", ['siswa', 'mahasiswa']);
            $table->string("student_identifier")->nullable();
            $table->string("full_name");
            $table->string("phone")->nullable();
            $table->text("address")->nullable();
            $table->string("bio")->nullable();
            $table->date("date_of_birth")->nullable();
            $table->timestamps();

            // $table->foreignId('users_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
