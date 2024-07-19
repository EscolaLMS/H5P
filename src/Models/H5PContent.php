<?php

namespace EscolaLms\HeadlessH5P\Models;

use EscolaLms\Core\Models\Traits\QueryCacheable;
use EscolaLms\Core\Models\User;
use EscolaLms\HeadlessH5P\Database\Factories\H5PContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *      schema="H5PContent",
 *      type="object",
 *      @OA\Property(
 *          property="id",
 *          description="Id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Date created at",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Date updated at",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="id of user that created/updated ",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          description="Title of new content",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="library_id",
 *          description="library_id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="parameters",
 *          description="JSON parameters",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="filtered",
 *          description="filtered",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="slug",
 *          description="slug",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="embed_type",
 *          description="embed type",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="params",
 *          description="parsed parameters",
 *          type="object"
 *      ),
 *      @OA\Property(
 *          property="metadata",
 *          description="parsed metadata",
 *          type="object"
 *      ),
 *      @OA\Property(
 *          property="library",
 *          description="library object",
 *          ref="#/components/schemas/H5PLibrary"
 *      ),
 *      @OA\Property(
 *          property="nonce",
 *          description="nonce taken from editor settings (random for new, hash for exisiting)",
 *          type="string"
 *      ),
 * )
 *
 * @property array $parameters
 * @property int $user_id
 * @property H5PLibrary $library
 * @property int $library_id
 */
class H5PContent extends Model
{
    use HasFactory, QueryCacheable;

    protected $table = 'hh5p_contents';

    protected $primaryKey = 'id';

    protected $guarded = ['id', 'uuid'];

    protected $appends = [
        'params',
        'metadata',
        'title'
    ];

    protected $visible = [
        'id',
        'uuid',
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
        'embed_type',
        'nonce'
    ];

    public function getTitleAttribute()
    {
        $parameters = json_decode($this->parameters);
        return $parameters->metadata->title ?? null;
    }

    public function getParamsAttribute()
    {
        $parameters = json_decode($this->parameters);
        return $parameters->params ?? $parameters;
    }

    public function getMetadataAttribute()
    {
        $parameters = json_decode($this->parameters);
        return $parameters->metadata ?? [];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }

    public function libraries(): HasMany
    {
        return $this->hasMany(H5PContentLibrary::class, 'content_id');
    }

    protected static function newFactory(): H5PContentFactory
    {
        return H5PContentFactory::new();
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string)Str::orderedUuid();
            }
        });

        static::retrieved(function ($model) {
            if (!$model->uuid) {
                $model->uuid = (string)Str::orderedUuid();
                $model->save();
            }
        });
    }
}
