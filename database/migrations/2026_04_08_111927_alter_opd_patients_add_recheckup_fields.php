<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opd_patients', function (Blueprint $table) {
            $table->enum('visit_type', ['new', 'follow_up', 'recheckup'])
                ->default('new')
                ->after('case_id');

            $table->unsignedBigInteger('parent_visit_id')
                ->nullable()
                ->after('visit_type');

            $table->unsignedBigInteger('root_visit_id')
                ->nullable()
                ->after('parent_visit_id');
        });

        DB::statement("UPDATE opd_patients SET root_visit_id = id WHERE root_visit_id IS NULL");

        Schema::table('opd_patients', function (Blueprint $table) {
            $table->foreign('parent_visit_id')
                ->references('id')
                ->on('opd_patients')
                ->nullOnDelete();

            $table->foreign('root_visit_id')
                ->references('id')
                ->on('opd_patients')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('opd_patients', function (Blueprint $table) {
            $table->dropForeign(['parent_visit_id']);
            $table->dropForeign(['root_visit_id']);

            $table->dropColumn([
                'visit_type',
                'parent_visit_id',
                'root_visit_id',
            ]);
        });
    }
};
