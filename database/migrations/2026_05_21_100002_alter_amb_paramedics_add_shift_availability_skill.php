<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amb_paramedics', function (Blueprint $t) {
            $t->enum('skill_level', ['BASIC', 'ADVANCED'])->default('BASIC')->after('certification');
            $t->enum('shift', ['MORNING', 'EVENING', 'NIGHT'])->nullable()->after('cert_expiry');
            $t->enum('availability', ['AVAILABLE', 'ASSIGNED', 'OFF_DUTY'])->default('AVAILABLE')->after('shift');
        });
    }

    public function down(): void
    {
        Schema::table('amb_paramedics', function (Blueprint $t) {
            $t->dropColumn(['skill_level', 'shift', 'availability']);
        });
    }
};
