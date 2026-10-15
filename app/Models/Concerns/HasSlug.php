<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model): void {
            if (blank($model->slug)) {
                $value = $model->{$model->slugSource()};

                if (filled($value)) {
                    $model->slug = $model->makeSlug($value);
                }
            }
        });
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    protected function makeSlug(string $value): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while ($this->slugExists($slug)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }
}
