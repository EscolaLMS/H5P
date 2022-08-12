<?php

namespace EscolaLms\HeadlessH5P\Tests\Fixture;

use Carbon\Carbon;
use DateTime;
use Faker\Factory as FakerFactory;
use stdClass;

class H5PContentTypeFixture
{
    private array $data = [];

    private int $count = 1;

    public static function fixture(): H5PContentTypeFixture
    {
        return new H5PContentTypeFixture();
    }

    public function count(int $count = 1): self
    {
        $this->count = $count;

        return $this;
    }

    public function make(): self
    {
        if ($this->count <= 1) {
            $this->data = $this->factory();

            return $this;
        }

        for ($i = 0; $i < $this->count; $i++)
        {
            $this->data[] = $this->factory();
        }

        return $this;
    }

    public function object(): stdClass|array
    {
        return json_decode(json_encode($this->data));
    }

    public function get(): array
    {
        return $this->data;
    }

    private function factory(): array
    {
        $faker = FakerFactory::create();

        return [
            'id' => $faker->word,
            'version' => [
                'major' => $faker->numberBetween(1, 10),
                'minor' => $faker->numberBetween(1, 10),
                'patch' => $faker->numberBetween(1, 10),
            ],
            'coreApiVersionNeeded' => [
                'major' => $faker->numberBetween(1, 10),
                'minor' => $faker->numberBetween(1, 10),
            ],
            'title' => $faker->word,
            'summary' => $faker->words(3, true),
            'description' => $faker->words(3, true),
            'icon' => $faker->url,
            'createdAt' => Carbon::now()->toISOString(),
            'updatedAt' =>  Carbon::now()->toISOString(),
            'isRecommended' => $faker->boolean,
            'popularity' => $faker->numberBetween(1, 10),
            'screenshots' => $faker->url,
            'license' => [],
            'example' => $faker->word,
            'tutorial' => $faker->url,
            'keywords' => [$faker->word],
            'categories' => [$faker->word],
            'owner' => $faker->firstName . ' ' . $faker->lastName
        ];
    }
}
