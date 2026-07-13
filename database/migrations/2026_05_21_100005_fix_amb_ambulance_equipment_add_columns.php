<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amb_ambulance_equipment', function (Blueprint $t) {
            $t->foreignId('ambulance_id')->after('id')->constrained('amb_ambulances')->cascadeOnDelete();
            $t->string('equipment_name');         // e.g. Oxygen, Ventilator, Defibrillator
            $t->string('equipment_type')->nullable(); // e.g. Life-Support, Monitoring
            $t->integer('quantity')->default(1);
            $t->enum('condition', ['GOOD', 'NEEDS_SERVICE', 'OUT_OF_ORDER'])->default('GOOD');
            $t->date('last_checked')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('amb_ambulance_equipment', function (Blueprint $t) {
            $t->dropForeign(['ambulance_id']);
            $t->dropColumn(['ambulance_id', 'equipment_name', 'equipment_type', 'quantity', 'condition', 'last_checked']);
        });
    }
};
