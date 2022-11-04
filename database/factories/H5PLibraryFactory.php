<?php

namespace EscolaLms\HeadlessH5P\Database\Factories;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PLibraryFactory extends Factory
{
    protected $model = H5PLibrary::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word . $this->faker->numberBetween(),
            'title' => 'title',
            'major_version' => 1,
            'minor_version' => 1,
            'patch_version' => 1,
            'runnable' => 1,
            'restricted' => 0,
            'fullscreen' => 1,
            'embed_types' => '',
            'preloaded_js' => '',
            'preloaded_css' => '',
            'drop_library_css' => '',
            'semantics' => '',
            'tutorial_url' => '',
            'has_icon' => 0,
            'add_to' => ''
        ];
    }
}
