<?php

namespace EscolaLms\HeadlessH5P\Database\Factories;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PContentLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentLibraryFactory extends Factory
{
    protected $model = H5PContentLibrary::class;

    public function definition()
    {
        return [
            'library_id' => H5PLibrary::factory(),
            'content_id' => H5PContent::factory(),
            'dependency_type' => $this->faker->word,
            'weight' => $this->faker->numberBetween(0, 10),
            'drop_css' => $this->faker->boolean
        ];
    }
}
