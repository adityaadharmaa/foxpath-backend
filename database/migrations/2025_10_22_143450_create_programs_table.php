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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description");

            $table->unsignedInteger("capacity")->default(0); //kuota magang
            $table->timestamp("registration_starts_at")->nullable();
            $table->timestamp("registration_ends_at")->nullable();

            $table->timestamp("cohot_starts_at")->nullable(); // tgl mulai dan berakhir magang
            $table->unsignedInteger("placement_duration_months")->default(6); // 6 bulan
            $table->boolean("is_active")->default(true);

            $table->timestamps();

            $table->index(["is_active"]);
            $table->index([ "registration_starts_at"]);
            $table->index([ "registration_ends_at"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
