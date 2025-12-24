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
        Schema::table('application_documents', function (Blueprint $table){
            $table->dropForeign(['verified_by']);

            $table->dropColumn([
                'is_verified',
                'verified_at',
                'verified_by',
                'verification_notes'
            ]);

            $table->enum('status', ['pending','approved','rejected'])
                ->default('pending')
                ->after('type');

            $table->text('review_note')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_documents', function (Blueprint $table){

        });
    }
};
