<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5PLibraryDependency extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'hh5p_libraries_dependencies';

    protected $fillable = [
        'library_id',
        'required_library_id',
        'dependency_type',
    ];

    protected $appends = [
        'dependencyType',
    ];

    protected $hidden = [
        'dependency_type',
    ];

    public function getDependencyTypeAttribute():string
    {
        return $this->attributes['dependency_type'];
    }

    public function library():BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }

    public function requiredLibrary():BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'required_library_id');
    }
}
