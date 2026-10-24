<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Agenda (Event) memiliki `published_at`, tetapi sebelumnya tidak pernah
 * difilter saat ditampilkan (temuan BUG-01 pada audit): agenda draf atau yang
 * dijadwalkan terbit di masa depan tetap muncul di /kegiatan dan dapat dibuka
 * lewat slug-nya.
 */
class EventPublicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Agenda yang belum diterbitkan tidak dapat dibuka lewat URL langsung.
     */
    public function test_agenda_yang_belum_terbit_menghasilkan_404(): void
    {
        $draf = $this->agenda(['title' => 'Agenda Draf', 'slug' => 'agenda-draf', 'published_at' => null]);
        $terjadwal = $this->agenda([
            'title' => 'Agenda Terjadwal',
            'slug' => 'agenda-terjadwal',
            'published_at' => now()->addWeek(),
        ]);

        $this->get(route('event.show', $draf->slug))->assertNotFound();
        $this->get(route('event.show', $terjadwal->slug))->assertNotFound();
    }

    /**
     * Agenda yang sudah terbit tetap dapat dibuka pengunjung.
     */
    public function test_agenda_yang_sudah_terbit_dapat_dibuka(): void
    {
        $event = $this->agenda([
            'title' => 'Agenda Publik',
            'slug' => 'agenda-publik',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('event.show', $event->slug))
            ->assertOk()
            ->assertSee('Agenda Publik');
    }

    /**
     * Daftar agenda hanya memuat agenda yang sudah terbit.
     */
    public function test_daftar_agenda_menyembunyikan_agenda_belum_terbit(): void
    {
        $this->agenda([
            'title' => 'Agenda Tampil',
            'slug' => 'agenda-tampil',
            'published_at' => now()->subDay(),
            'start_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->agenda([
            'title' => 'Agenda Draf Tersembunyi',
            'slug' => 'agenda-draf-tersembunyi',
            'published_at' => null,
            'start_date' => now()->addDays(4)->toDateString(),
        ]);

        $this->agenda([
            'title' => 'Agenda Masa Depan Tersembunyi',
            'slug' => 'agenda-masa-depan-tersembunyi',
            'published_at' => now()->addWeek(),
            'start_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->get('/kegiatan?tab=agenda')
            ->assertOk()
            ->assertSee('Agenda Tampil')
            ->assertDontSee('Agenda Draf Tersembunyi')
            ->assertDontSee('Agenda Masa Depan Tersembunyi');
    }

    /**
     * Agenda selesai yang belum terbit juga tidak masuk daftar "telah
     * terlaksana" pada tab kegiatan.
     */
    public function test_agenda_selesai_yang_belum_terbit_tidak_muncul(): void
    {
        $this->agenda([
            'title' => 'Agenda Selesai Terbit',
            'slug' => 'agenda-selesai-terbit',
            'status' => 'completed',
            'published_at' => now()->subDay(),
            'start_date' => now()->subMonth()->toDateString(),
        ]);

        $this->agenda([
            'title' => 'Agenda Selesai Draf',
            'slug' => 'agenda-selesai-draf',
            'status' => 'completed',
            'published_at' => null,
            'start_date' => now()->subMonths(2)->toDateString(),
        ]);

        $this->get('/kegiatan?tab=agenda')
            ->assertOk()
            ->assertSee('Agenda Selesai Terbit')
            ->assertDontSee('Agenda Selesai Draf');
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function agenda(array $atribut): Event
    {
        return Event::query()->create(array_merge([
            'title' => 'Agenda Uji',
            'slug' => 'agenda-uji',
            'status' => 'upcoming',
            'start_date' => now()->addDays(3)->toDateString(),
            'published_at' => now()->subDay(),
        ], $atribut));
    }
}
