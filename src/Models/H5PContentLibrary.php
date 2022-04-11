<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5PContentLibrary extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'hh5p_contents_libraries';

    protected $fillable = [
        'content_id',
        'library_id',
        'dependency_type',
        'weight',
        'drop_css'
    ];

    protected $appends = [
        'dependencyType',
        'dropCss'
    ];

    protected $hidden = [
        'dependency_type',
    ];

    protected $casts = [];

    public function getDependencyTypeAttribute():string
    {
        return $this->attributes['dependency_type'];
    }

    public function getDropCssAttribute():string
    {
        return $this->attributes['drop_css'];
    }

    public function library():BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }

    public function content():BelongsTo
    {
        return $this->belongsTo(H5PContent::class, 'content_id');
    }
}
