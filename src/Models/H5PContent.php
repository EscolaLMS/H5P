<?php

namespace EscolaLms\HeadlessH5P\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

//use App\Models\User;

class H5PContent extends Model
{
    protected $table = 'hh5p_contents';

    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    protected $appends = [
        'params',
        'metadata'
    ];

    protected $visible = [
        'id',
        'created_at',
        'updated_at',
        'user_id',
        'title',
        'library_id',
        'library',
        'user',
        'parameters',
        'params',
        'metadata',
        'slug',
        'filtered',
        'disable',
        'embed_type'
    ];

    public function getParamsAttribute($value)
    {
        $parameters = json_decode($this->parameters);
        return $parameters->params;
    }


    public function getMetadataAttribute($value)
    {
        $parameters = json_decode($this->parameters);
        return $parameters->metadata;
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function library():BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }
}
