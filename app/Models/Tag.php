<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug'];

    protected function slugSource(): string
    {
        return 'name';
    }
}
