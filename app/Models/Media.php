<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'model_type', 'model_id', 'collection', 'name', 'file_name',
        'mime_type', 'path', 'disk', 'size', 'alt', 'sort_order',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}
