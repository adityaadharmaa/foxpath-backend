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
        Schema::table('internship_applications', function (Blueprint $table){
            $table->enum('status', [
                'draft',
                'submitted',
                'verified',
                'scored',
                'accepted',
                'rejected',
            ])->default('draft')->change();

            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            $table->timestamp('scored_at')->nullable()->after('verified_at');
            $table->timestamp('decided_at')->nullable()->after('scored_at');

            $table->unique(['programs_id', 'users_id'], 'unique_user_program_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            $table->dropUnique('unique_user_program_application');

            $table->dropColumn('verified_at', 'scored_at', 'decided_at');
        });
    }
};
