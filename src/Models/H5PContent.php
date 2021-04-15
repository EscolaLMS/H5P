<?php

namespace EscolaLms\HeadlessH5P\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//use App\Models\User;

class H5PContent extends Model
{
    protected $table = 'hh5p_contents';

    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'title',
        'library_id',
        'parameters',
        'filtered',
        'slug',
        'embed_type',
        'disable',
        'content_type',
        'author',
        'source',
        'year_from',
        'year_to',
        'license',
        'license_version',
        'license_extras',
        'author_comments',
        'changes',
        'default_languge',
        'keywords',
        'description',
    ];

    public function getParametersAttribute($value)
    {
        return json_decode($value);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function library():BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class);
    }
}
