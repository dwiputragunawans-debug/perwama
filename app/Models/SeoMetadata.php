<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = [
        'model_type', 'model_id', 'meta_title', 'meta_description',
        'meta_keywords', 'og_image', 'canonical_url',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
