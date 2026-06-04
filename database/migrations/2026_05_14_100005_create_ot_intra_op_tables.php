<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_anesthesia_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->unsignedBigInteger('anesthesia_type_id')->nullable();
            $table->unsignedBigInteger('anesthetist_id')->nullable();
            $table->dateTime('induction_time')->nullable();
            $table->dateTime('recovery_time')->nullable();
            $table->text('pre_anesthesia_assessment')->nullable();
            $table->text('drugs_used')->nullable();
            $table->text('airway_management')->nullable();
            $table->json('intra_op_vitals')->nullable();
            $table->text('complications')->nullable();
            $table->text('post_anesthesia_notes')->nullable();
            $table->text('asa_grade')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->foreign('anesthesia_type_id')->references('id')->on('ot_anesthesia_types')->onDelete('set null');
        });

        Schema::create('ot_intra_op_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->dateTime('incision_time')->nullable();
            $table->dateTime('closure_time')->nullable();
            $table->text('operative_findings')->nullable();
            $table->text('procedure_performed')->nullable();
            $table->text('operative_notes')->nullable();
            $table->text('specimens_collected')->nullable();
            $table->text('implants_used')->nullable();
            $table->decimal('blood_loss_ml', 8, 2)->nullable();
            $table->decimal('blood_transfused_ml', 8, 2)->nullable();
            $table->text('complications')->nullable();
            $table->text('post_op_instructions')->nullable();
            $table->boolean('counts_verified')->default(false);
            $table->unsignedBigInteger('signed_by')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->unique('surgery_schedule_id');
        });

        Schema::create('ot_consumable_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->unsignedBigInteger('ot_consumable_id')->nullable();
            $table->string('item_name');
            $table->string('item_code', 50)->nullable();
            $table->string('type', 30)->default('consumable');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 20)->nullable();
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('is_billed')->default(false);
            $table->unsignedBigInteger('patient_charge_id')->nullable();
            $table->boolean('inventory_deducted')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->foreign('ot_consumable_id')->references('id')->on('ot_consumables')->onDelete('set null');
            $table->index(['surgery_schedule_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_consumable_usages');
        Schema::dropIfExists('ot_intra_op_records');
        Schema::dropIfExists('ot_anesthesia_records');
    }
};
