<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $path
 */
class H5PTempFile extends Model
{
    protected $table = 'hh5p_temp_files';

    protected $fillable = [
        'path',
        'nonce',
        'created_at',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(H5PContent::class, 'nonce', 'nonce');
    }
}
