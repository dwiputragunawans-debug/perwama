<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('member_number')->nullable()->unique();
            $table->string('full_name');
            $table->string('nik')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->string('interest_field')->nullable();
            $table->string('maluku_origin')->nullable();
            $table->string('domicile_region')->nullable();
            $table->string('photo')->nullable();
            $table->string('status')->default('pending'); // pending | active | inactive | rejected
            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('nik')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->string('interest_field')->nullable();
            $table->string('maluku_origin')->nullable();
            $table->string('domicile_region')->nullable();
            $table->string('photo')->nullable();
            $table->string('membership_type')->default('anggota'); // anggota | relawan | mitra | kolaborasi
            $table->text('motivation')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
        Schema::dropIfExists('members');
    }
};
