<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'mime_type',
        'size',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
