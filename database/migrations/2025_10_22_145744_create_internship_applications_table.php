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
        Schema::create('internship_applications', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger("programs_id");
            // $table->unsignedBigInteger("profiles_id");
            $table->foreignId("programs_id")->references("id")->on("programs");
            $table->foreignId("profiles_id")->references("id")->on("profiles");
            $table->enum("status", [
                "DRAFT", "SUBMITTED", "UNDER_REVIEW", "PASSED", "WAITLIST", "REJECTED", "WITHDRAWN"
            ])->default("DRAFT")->index();
            $table->timestamp("submitted_at")->nullable();

            $table->decimal("final_score", 10, 6)->nullable()->index();
            $table->unsignedInteger("rank")->nullable()->index();

            $table->timestamp("admitted_at")->nullable();
            $table->timestamp("placement_start_at")->nullable();
            $table->timestamp("placement_end_at")->nullable();
            $table->timestamps();

            $table->unique(["programs_id","profiles_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_applications');
    }
};
