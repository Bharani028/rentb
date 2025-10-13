<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Moderation fields
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                  ->constrained('users')->nullOnDelete();

            // Analytics
            $table->unsignedBigInteger('view_count')->default(0)->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Drop FKs in correct order
            if (Schema::hasColumn('properties', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            $table->dropColumn([
                'rejection_reason',
                'approved_at',
                'view_count',
            ]);
        });
    }
};
