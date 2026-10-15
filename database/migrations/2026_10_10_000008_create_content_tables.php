<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('kisah_inspiratif'); // kisah_inspiratif | tokoh_perempuan | perempuan_maluku | cerita_komunitas
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('person_name')->nullable();
            $table->string('person_photo')->nullable();
            $table->unsignedTinyInteger('person_age')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('quote')->nullable();
            $table->longText('journey')->nullable();
            $table->longText('activities')->nullable();
            $table->longText('impact')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('berita'); // berita | artikel | opini | pengumuman
            $table->string('category')->nullable(); // organisasi | kegiatan | sosial | pendidikan | perempuan | ekonomi | hukum_ham | budaya | pengumuman
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('author')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable(); // laporan | buku | modul | materi_pendidikan | kajian | dokumentasi | panduan
            $table->text('description')->nullable();
            $table->string('file')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('year')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
        Schema::dropIfExists('news');
        Schema::dropIfExists('stories');
    }
};
