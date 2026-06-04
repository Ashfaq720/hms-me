<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enrich floors
        Schema::table('floors', function (Blueprint $t) {
            if (! Schema::hasColumn('floors', 'code')) {
                $t->string('code', 32)->nullable()->after('name');
            }
            if (! Schema::hasColumn('floors', 'building')) {
                $t->string('building', 100)->nullable()->after('code');
            }
            if (! Schema::hasColumn('floors', 'description')) {
                $t->string('description', 255)->nullable()->after('building');
            }
        });

        // 2. Enrich bed_groups (Ward / Cabin Block / Wing)
        Schema::table('bed_groups', function (Blueprint $t) {
            if (! Schema::hasColumn('bed_groups', 'code')) {
                $t->string('code', 32)->nullable()->after('name');
            }
            if (! Schema::hasColumn('bed_groups', 'group_type')) {
                // ward, cabin_block, icu_wing, nicu_wing, ccu_wing, isolation, recovery, day_care
                $t->string('group_type', 30)->default('ward')->after('code');
            }
            if (! Schema::hasColumn('bed_groups', 'gender_preference')) {
                $t->enum('gender_preference', ['any', 'male', 'female', 'mixed'])->default('any')->after('group_type');
            }
            if (! Schema::hasColumn('bed_groups', 'notes')) {
                $t->string('notes', 255)->nullable()->after('gender_preference');
            }
        });

        // 3. Enrich bed_types — base rate per bed type
        Schema::table('bed_types', function (Blueprint $t) {
            if (! Schema::hasColumn('bed_types', 'base_rent')) {
                $t->decimal('base_rent', 12, 2)->default(0)->after('name');
            }
            if (! Schema::hasColumn('bed_types', 'description')) {
                $t->string('description', 255)->nullable()->after('base_rent');
            }
            if (! Schema::hasColumn('bed_types', 'default_package_id')) {
                $t->unsignedInteger('default_package_id')->nullable()->after('description');
            }
        });

        // 4. rooms — simple canonical shape (id / name / floor_id / is_active).
        //    The codebase + seeders use Floor → Room → BedGroup → Bed.
        //    A later migration (2026_05_21_100000_create_rooms_table) is
        //    idempotent and skips when this has already created the table.
        if (! Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->foreignId('floor_id')->constrained('floors')->cascadeOnDelete();
                $t->integer('is_active')->default(1);
                $t->timestamps();
            });
        }

        // 5. Beds — add room_id + extra pricing axes + package linkage
        Schema::table('beds', function (Blueprint $t) {
            if (! Schema::hasColumn('beds', 'room_id')) {
                $t->foreignId('room_id')->nullable()->after('bed_group_id')->constrained('rooms')->nullOnDelete();
            }
            if (! Schema::hasColumn('beds', 'bed_no')) {
                $t->string('bed_no', 20)->nullable()->after('name');
            }
            if (! Schema::hasColumn('beds', 'amenity_charge')) {
                $t->decimal('amenity_charge', 12, 2)->default(0)->after('rent');
            }
            if (! Schema::hasColumn('beds', 'nursing_charge')) {
                $t->decimal('nursing_charge', 12, 2)->default(0)->after('amenity_charge');
            }
            if (! Schema::hasColumn('beds', 'status')) {
                $t->enum('status', ['available', 'occupied', 'reserved', 'maintenance', 'cleaning'])
                  ->default('available')->after('is_reserved');
            }
            if (! Schema::hasColumn('beds', 'default_package_id')) {
                $t->unsignedInteger('default_package_id')->nullable()->after('status');
            }
        });

        // 6. NEW: package_beds — many-to-many "this package applies to these beds/rooms"
        if (! Schema::hasTable('package_bed_links')) {
            Schema::create('package_bed_links', function (Blueprint $t) {
                $t->id();
                $t->unsignedInteger('package_id');
                $t->unsignedBigInteger('bed_type_id')->nullable();
                $t->unsignedBigInteger('room_id')->nullable();
                $t->unsignedBigInteger('bed_id')->nullable();
                $t->decimal('override_price', 12, 2)->nullable();
                $t->boolean('is_default')->default(false);
                $t->timestamps();
                $t->index(['package_id']);
                $t->index(['bed_type_id']);
                $t->foreign('package_id')->references('id')->on('packages')->cascadeOnDelete();
                $t->foreign('bed_type_id')->references('id')->on('bed_types')->nullOnDelete();
                $t->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
                $t->foreign('bed_id')->references('id')->on('beds')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('package_bed_links')) {
            Schema::drop('package_bed_links');
        }
        Schema::table('beds', function (Blueprint $t) {
            foreach (['room_id', 'bed_no', 'amenity_charge', 'nursing_charge', 'status', 'default_package_id'] as $c) {
                if (Schema::hasColumn('beds', $c)) {
                    try { $t->dropConstrainedForeignId($c); } catch (\Throwable $e) {}
                    try { $t->dropColumn($c); } catch (\Throwable $e) {}
                }
            }
        });
        if (Schema::hasTable('rooms')) {
            Schema::drop('rooms');
        }
        Schema::table('bed_types', function (Blueprint $t) {
            foreach (['base_rent', 'description', 'default_package_id'] as $c) {
                if (Schema::hasColumn('bed_types', $c)) {
                    try { $t->dropColumn($c); } catch (\Throwable $e) {}
                }
            }
        });
        Schema::table('bed_groups', function (Blueprint $t) {
            foreach (['code', 'group_type', 'gender_preference', 'notes'] as $c) {
                if (Schema::hasColumn('bed_groups', $c)) {
                    try { $t->dropColumn($c); } catch (\Throwable $e) {}
                }
            }
        });
        Schema::table('floors', function (Blueprint $t) {
            foreach (['code', 'building', 'description'] as $c) {
                if (Schema::hasColumn('floors', $c)) {
                    try { $t->dropColumn($c); } catch (\Throwable $e) {}
                }
            }
        });
    }
};
