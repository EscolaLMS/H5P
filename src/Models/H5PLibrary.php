<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;

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

    
    protected $appends = [
        'machineName',
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
        

    public function getLibraryIdAttribute()
    {
        return $this->getKey();

        //return $this->attributes['id'];
        //return $this->getAttributeValue('id');
        return isset($this->attributes['id']) ? $this->attributes['id'] : '';
        return $this->attributes['id'];
    }

    public function getMachineNameAttribute():string
    {
        return $this->getAttributeValue('name');
        return isset($this->attributes['name']) ? $this->attributes['name'] : '';
        return $this->attributes['name'];
    }
    
    public function getMajorVersionAttribute():int
    {
        //return $this->getAttributeValue('major_version');

        return isset($this->attributes['major_version']) ? $this->attributes['major_version'] : 0;
    }
    
    public function getMinorVersionAttribute():int
    {
        return isset($this->attributes['minor_version']) ? $this->attributes['minor_version'] : '';
        return $this->attributes['minor_version'];
    }

    public function getPatchVersionAttribute():int
    {
        return isset($this->attributes['patch_version']) ? $this->attributes['patch_version'] : '';
        return $this->attributes['patch_version'];
    }

    public function getPreloadedJsAttribute():string
    {
        return isset($this->attributes['preloaded_js']) ? $this->attributes['preloaded_js'] : '';
        return $this->attributes['preloaded_js'];
    }

    public function getPreloadedCssAttribute():string
    {
        return isset($this->attributes['preloaded_css']) ? $this->attributes['preloaded_css'] : '';
        return $this->attributes['preloaded_css'];
    }

    public function getDropLibraryCssAttribute():string
    {
        return isset($this->attributes['drop_library_css']) ? $this->attributes['drop_library_css'] : '';
        return $this->attributes['drop_library_css'];
    }

    public function getTutorialUrlAttribute():string
    {
        return isset($this->attributes['tutorial_url']) ? $this->attributes['tutorial_url'] : '';
        return $this->attributes['tutorial_url'];
    }
    
    public function getHasIconAttribute():string
    {
        return isset($this->attributes['has_icon']) ? $this->attributes['has_icon'] : '';
        return $this->attributes['has_icon'];
    }

    public function dependencies()
    {
        return $this->hasMany(H5PLibraryDependency::class, 'library_id');
    }
}
