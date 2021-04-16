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
    protected $guarded = ['id'];

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
