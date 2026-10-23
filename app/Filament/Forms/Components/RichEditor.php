<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\RichEditor as FilamentRichEditor;
use Filament\Forms\Components\RichEditor\RichEditorTool;

/**
 * Editor teks kaya (RichEditor) untuk seluruh kolom deskripsi di panel admin.
 *
 * Filament hanya menampilkan tombol rata kiri, rata tengah, dan rata kanan
 * secara bawaan, padahal TipTap (mesin editor yang dipakai) sudah mendukung
 * perataan "justify". Kelas ini menambahkan tombol tersebut agar pengelola
 * dapat memilih teks rata kanan kiri.
 */
class RichEditor extends FilamentRichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        // Istilah bawaan Filament untuk perataan ini adalah "Rata penuh".
        // Pengelola mengenalnya sebagai "rata kanan kiri", jadi labelnya
        // disesuaikan tanpa mengubah perilaku tombol bawaan.
        foreach ($this->tools as $tool) {
            if ($tool instanceof RichEditorTool && $tool->getName() === 'alignJustify') {
                $tool->label('Rata kanan kiri');
            }
        }
    }

    /**
     * @return array<string | array<string>>
     */
    public function getDefaultToolbarButtons(): array
    {
        return array_map(
            static function (array|string $group): array|string {
                $position = is_array($group) ? array_search('alignEnd', $group, true) : false;

                if ($position === false) {
                    return $group;
                }

                array_splice($group, $position + 1, 0, ['alignJustify']);

                return $group;
            },
            parent::getDefaultToolbarButtons(),
        );
    }
}
