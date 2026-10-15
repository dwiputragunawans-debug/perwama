<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_category_id')->nullable()->constrained('program_categories')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('lainnya'); // kerja_anggaran | lainnya
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->text('goal')->nullable();
            $table->text('objectives')->nullable();
            $table->text('target_participants')->nullable();
            $table->text('achievements')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('document')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('fiscal_year')->nullable();
            $table->string('status')->default('planned'); // planned | ongoing | completed
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
        Schema::dropIfExists('program_categories');
    }
};
