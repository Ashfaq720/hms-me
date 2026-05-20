<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_visitors', function (Blueprint $table) {
            $table->id();

            // Visitor info
            $table->string('visitor_name');
            $table->string('contact_no', 20)->nullable();
            $table->unsignedSmallInteger('visitor_qty')->default(1);

            // Patient visit context
            $table->enum('patient_type', ['OPD', 'Ipd', 'ER'])->nullable();
            $table->date('visit_date'); // you can change to dateTime if you want entry time too

            // Link to department (from DB)
            $table->foreignId('department_id')
                ->constrained('departments')
                ->nullable()
                ->restrictOnDelete();

            // Optional patient link (if selected from patient dropdown)
            $table->foreignId('patient_id')
                ->nullable()
                ->constrained('patients')
                ->nullOnDelete();

            // Manual patient name (if not selected from DB)
            $table->string('patient_name')->nullable();

            // Optional notes
            $table->text('remarks')->nullable();

                                                                    // Optional: unique visit slip/pass number
            $table->string('visit_code', 30)->nullable()->unique(); // ex: VIS-000001
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Helpful indexes
            $table->index(['visit_date', 'patient_type']);
            $table->index('department_id');
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_visitors');
    }
};
