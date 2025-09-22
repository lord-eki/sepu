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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('membership_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('id_number')->unique();
            $table->enum('id_type', ['national_id', 'passport', 'driving_license']);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->text('physical_address');
            $table->string('postal_address')->nullable();
            $table->string('city');
            $table->string('county');
            $table->string('country')->default('Kenya');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship');
            $table->enum('membership_status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->date('membership_date');
            $table->string('profile_photo')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
