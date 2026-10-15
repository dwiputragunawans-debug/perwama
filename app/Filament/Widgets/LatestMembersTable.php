<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Str;

/**
 * Daftar "Pendaftaran Anggota Terbaru" pada dasbor panel.
 *
 * Menampilkan lima pendaftar terakhir beserta domisili dan statusnya, dengan
 * tautan "Lihat semua" menuju daftar Anggota di panel.
 */
class LatestMembersTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    /**
     * Dua dari tiga kolom dasbor, agar berdampingan dengan grafik
     * "Pertumbuhan Anggota" (lihat App\Filament\Pages\Dashboard).
     */
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pendaftaran Anggota Terbaru')
            ->query(Member::query()->latest('created_at')->limit(5))
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->html()
                    ->formatStateUsing(fn (Member $record): string => $this->namaDenganInisial($record)),
                TextColumn::make('domisili')
                    ->label('Domisili (Wilayah)')
                    ->state(fn (Member $record): string => $this->domisili($record)),
                TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->formatStateUsing(fn (mixed $state): string => tanggal_id($state, 'j M Y')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (Member $record): string => $record->statusLabel())
                    ->color(fn (Member $record): string => match ($record->status) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->paginated(false)
            ->headerActions([
                Action::make('lihatSemua')
                    ->label('Lihat semua')
                    ->url(MemberResource::getUrl('index'))
                    ->link(),
            ]);
    }

    /**
     * Nama anggota dengan bulatan inisial di depannya (lihat gaya
     * `.dashboard-member` pada resources/views/filament/appearance-styles).
     */
    private function namaDenganInisial(Member $member): string
    {
        return '<span class="dashboard-member">'
            .'<span class="dashboard-member-avatar">'.e($this->inisial($member->full_name)).'</span>'
            .'<span class="dashboard-member-name">'.e($member->full_name).'</span>'
            .'</span>';
    }

    /**
     * Dua huruf pertama dari dua kata pertama nama anggota, mis. "Siti Maimuna"
     * menjadi "SM". Nama satu kata memakai dua huruf pertamanya.
     */
    private function inisial(string $nama): string
    {
        $kata = preg_split('/\s+/', trim($nama)) ?: [];

        if (count($kata) === 1) {
            return Str::upper(Str::substr($kata[0], 0, 2));
        }

        return Str::upper(Str::substr($kata[0], 0, 1).Str::substr($kata[1], 0, 1));
    }

    /**
     * Domisili anggota: wilayah pada data induk bila ada, lalu isian domisili,
     * kota, dan provinsi sebagai cadangan.
     */
    private function domisili(Member $member): string
    {
        foreach ([$member->region?->name, $member->domicile_region, $member->city, $member->province] as $nilai) {
            if (filled($nilai)) {
                return (string) $nilai;
            }
        }

        return '—';
    }
}
