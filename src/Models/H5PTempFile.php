<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use EscolaLms\HeadlessH5P\Models\H5PContent;

class H5PTempFile extends Model
{
    protected $table = 'hh5p_temp_files';
    protected $fillable = [
        'path',
        'nonce',
        'created_at',
    ];

    public function content():BelongsTo
    {
        return $this->belongsTo(H5PContent::class, 'nonce');
    }
}
