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
        Schema::table('application_documents', function (Blueprint $table) {
            $table->boolean('is_verified')
            ->default(false)
            ->after('size');

            $table->timestamp('verified_at')
            ->nullable()
            ->after('is_verified');

            $table->unsignedBigInteger('verified_by')
            ->nullable()
            ->after('verified_at');

            $table->text('verification_notes')
            ->nullable()
            ->after('verified_by');

            $table->foreign('verified_by')
            ->references('id')
            ->on('users')
            ->onDelete('set null');

            $table->unique(
                ['internship_applications_id', 'type'],
                'application_documents_unique_internship_type'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_documents', function (Blueprint $table) {

            $table->dropUnique('application_documents_unique_application_type');

            $table->dropForeign(['verified_by']);

            $table->dropColumn([
                'is_verified',
                'verified_at',
                'verified_by',
                'verification_note',
            ]);
        });
    }
};
