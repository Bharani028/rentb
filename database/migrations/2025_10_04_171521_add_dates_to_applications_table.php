<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_dates_to_applications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('phone');
            $table->date('end_date')->nullable()->after('start_date');

            // (nice to have) indexes to speed queries
            $table->index(['property_id', 'applicant_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['start_date','end_date']);
            $table->dropIndex(['applications_property_id_applicant_id_index']);
            $table->dropIndex(['applications_status_index']);
        });
    }
};
