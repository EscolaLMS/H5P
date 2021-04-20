<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;

/**
 * @OA\Schema(
 *      schema="H5PLibrary",
 *      type="object",
 *      @OA\Property(
 *          property="id",
 *          description="ID of Content in DB",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Machine name. Alias",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="runnable",
 *          description="Can be selected from editor dropdown list",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="restricted",
 *          description="",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="fullscreen",
 *          description="",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="embed_types",
 *          description="Either div or iframe",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="semantics",
 *          description="",
 *          type="object",
 *      ),
 *      @OA\Property(
 *          property="machineName",
 *          description="Machine Name",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="uberName",
 *          description="params taken from editor",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="majorVersion",
 *          description="major version",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="minorVersion",
 *          description="minor version",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="patchVersion",
 *          description="Patch Version",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="preloadedJs",
 *          description="Comma separated list of JavaScript dependencies",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="preloadedCss",
 *          description="Comma separated list of CSS dependencies",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="dropLibraryCss",
 *          description="",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="tutorialUrl",
 *          description="",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="hasIcon",
 *          description="",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="libraryId",
 *          description="ID of library. Alias",
 *          type="integer",
 *      )
 * )
 */

class H5PLibrary extends Model
{
    protected $table = 'hh5p_libraries';
    //protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'title',
        'major_version',
        'minor_version',
        'patch_version',
        'runnable',
        'restricted',
        'fullscreen',
        'embed_types',
        'preloaded_js',
        'preloaded_css',
        'drop_library_css',
        'semantics',
        'tutorial_url',
        'has_icon',
        'created_at',
        'updated_at',
    ];

    protected $visible = [
        'id',
        'name',
        'title',
        'major_version',
        'minor_version',
        'patch_version',
        'runnable',
        'restricted',
        'fullscreen',
        'embed_types',
        'preloaded_js',
        'preloaded_css',
        'drop_library_css',
        'semantics',
        'tutorial_url',
        'has_icon',
        'created_at',
        'updated_at',
        'machineName',
        'uberName',
        'majorVersion',
        'minorVersion',
        'patchVersion',
        'preloadedJs',
        'preloadedCss',
        'dropLibraryCss',
        'tutorialUrl',
        'hasIcon',
        'libraryId',
        'children',
        'languages'
    ];
    
    protected $appends = [
        'machineName',
        'uberName',
        'majorVersion',
        'minorVersion',
        'patchVersion',
        'preloadedJs',
        'preloadedCss',
        'dropLibraryCss',
        'tutorialUrl',
        'hasIcon',
        'libraryId'
    ];
    
    protected $hidden = [
        'major_version',
        'minor_version',
        'patch_version',
        'preloaded_js',
        'preloaded_css',
        'drop_library_css',
        'tutorial_url',
        'has_icon',
    ];
        

    public function getSemanticsAttribute($value)
    {
        return json_decode($value);
    }
    
    public function getLibraryIdAttribute()
    {
        return $this->getKey();
        return isset($this->attributes['id']) ? $this->attributes['id'] : '';
    }

    public function getMachineNameAttribute():string
    {
        return $this->getAttributeValue('name');
        return isset($this->attributes['name']) ? $this->attributes['name'] : '';
        return $this->attributes['name'];
    }

    public function getUberNameAttribute():string
    {
        return $this->getAttributeValue('name')." ".$this->getAttributeValue('major_version').".".$this->getAttributeValue('minor_version');
    }
    
    public function getMajorVersionAttribute():int
    {
        return isset($this->attributes['major_version']) ? $this->attributes['major_version'] : 0;
    }
    
    public function getMinorVersionAttribute():int
    {
        return isset($this->attributes['minor_version']) ? $this->attributes['minor_version'] : '';
    }

    public function getPatchVersionAttribute():int
    {
        return isset($this->attributes['patch_version']) ? $this->attributes['patch_version'] : '';
    }

    public function getPreloadedJsAttribute():string
    {
        return isset($this->attributes['preloaded_js']) ? $this->attributes['preloaded_js'] : '';
    }

    public function getPreloadedCssAttribute():string
    {
        return isset($this->attributes['preloaded_css']) ? $this->attributes['preloaded_css'] : '';
    }

    public function getDropLibraryCssAttribute():string
    {
        return isset($this->attributes['drop_library_css']) ? $this->attributes['drop_library_css'] : '';
    }

    public function getTutorialUrlAttribute():string
    {
        return isset($this->attributes['tutorial_url']) ? $this->attributes['tutorial_url'] : '';
    }
    
    public function getHasIconAttribute():string
    {
        return isset($this->attributes['has_icon']) ? $this->attributes['has_icon'] : '';
    }

    public function dependencies()
    {
        return $this->hasMany(H5PLibraryDependency::class, 'library_id');
    }

    public function children()
    {
        return $this->belongsToMany(H5PLibrary::class, 'hh5p_libraries_dependencies', 'library_id', 'required_library_id')->with('children');
    }


    public function languages()
    {
        return $this->hasMany(H5PLibraryLanguage::class, 'library_id');
    }
}
