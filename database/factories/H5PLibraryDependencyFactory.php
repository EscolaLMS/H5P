<?php

namespace EscolaLms\HeadlessH5P\Database\Factories;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PLibraryDependencyFactory extends Factory
{
    protected $model = H5PLibraryDependency::class;

    public function definition()
    {
        return [
            'library_id' => H5PLibrary::factory(),
            'required_library_id' => H5PLibrary::factory(),
            'dependency_type' => $this->faker->randomElement(['editor', 'preloaded'])
        ];
    }
}
