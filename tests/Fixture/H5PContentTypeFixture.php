<?php

namespace EscolaLms\HeadlessH5P\Tests\Fixture;

use Carbon\Carbon;
use DateTime;
use Faker\Factory as FakerFactory;
use stdClass;

class H5PContentTypeFixture
{
    private stdClass $data;

    private int $count = 1;

    public function __construct()
    {
        $this->data = new stdClass();
    }

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
            $this->data->contentTypes = $this->factory();

            return $this;
        }

        for ($i = 0; $i < $this->count; $i++)
        {
            $this->data->contentTypes[] = $this->factory();
        }

        return $this;
    }

    public function get(): stdClass
    {
        return $this->data;
    }

    private function factory(): stdClass
    {
        $faker = FakerFactory::create();

        $data = new stdClass();
        $data->id = $faker->word;
        $data->version = new stdClass();
        $data->version->major = $faker->numberBetween(1, 10);
        $data->version->minor = $faker->numberBetween(1, 10);
        $data->version->patch = $faker->numberBetween(1, 10);
        $data->coreApiVersionNeeded = new stdClass();
        $data->coreApiVersionNeeded->major = $faker->numberBetween(1, 10);
        $data->coreApiVersionNeeded->minor = $faker->numberBetween(1, 10);
        $data->title = $faker->word;
        $data->summary = $faker->words(3, true);
        $data->description = $faker->words(3, true);
        $data->icon = $faker->url;
        $data->createdAt = Carbon::now()->toISOString();
        $data->updatedAt =  Carbon::now()->toISOString();
        $data->isRecommended = $faker->boolean;
        $data->popularity = $faker->numberBetween(1, 10);
        $data->screenshots = $faker->url;
        $data->license = array();
        $data->example = $faker->word;
        $data->tutorial = $faker->url;
        $data->keywords = array($faker->word);
        $data->categories = array($faker->word);
        $data->owner = $faker->firstName . ' ' . $faker->lastName;

        return $data;
    }
}
