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
        Schema::create('profile_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId("profiles_id")->references("id")->on("profiles");
            // $table->unsignedBigInteger("profiles_id");
            $table->string("institution_name_raw");
            $table->string("institution_name_canonical", 191)->nullable()->index();
            $table->string("institution_slug", 191)->nullable()->index();
            $table->enum("level", ["SMK", "D3", "D4", "S1", "S2", "S3"])->index();
            $table->string("program")->nullable();
            $table->boolean("is_current")->default(true);   
            $table->timestamps();


            $table->index(["profiles_id", "is_current"]);
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