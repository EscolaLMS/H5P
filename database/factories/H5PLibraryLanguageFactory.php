<?php

namespace EscolaLms\HeadlessH5P\Database\Factories;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PLibraryLanguageFactory extends Factory
{
    protected $model = H5PLibraryLanguage::class;

    public static string $language_code = 'en';

    public function definition()
    {
        $code = self::$language_code ?? $this->faker->randomElement(['pl', 'de', 'fr', 'es', 'en']);
        return [
            'library_id' => H5PLibrary::factory(),
            'language_code' => $code,
            'translation' => $this->getTranslation($code),
        ];
    }

    public function languageCode(string $code): self
    {
        self::$language_code = $code;
        return $this;
    }

    private function getTranslation(string $code): string
    {
        $translations = [
            [
                'code' => 'en',
                'text' => 'Sample translation'
            ],
            [
                'code' => 'pl',
                'text' => 'Przykładowe tłumaczenie'
            ],
            [
                'code' => 'de',
                'text' => 'Beispielubersetzung'
            ],
            [
                'code' => 'es',
                'text' => 'Ejemplo de traduccion'
            ],
            [
                'code' => 'fr',
                'text' => 'Exemple de traduction'
            ]
        ];

        return json_encode(array_filter($translations, fn($item) => $item['code'] === $code));
    }
}
