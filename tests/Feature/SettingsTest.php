<?php

namespace EscolaLms\HeadlessH5P\Tests\Feature;

use EscolaLms\HeadlessH5P\Enums\ConfigEnum;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;

class SettingsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(EscolaLmsSettingsServiceProvider::class)) {
            $this->markTestSkipped('Settings package not installed');
        }

        $this->seed(PermissionTableSeeder::class);
        Config::set('escola_settings.use_database', true);
        $this->authenticateAsAdmin();
    }

    public function testAdministrableConfigApi(): void
    {
        $this->actingAs($this->user, 'api')->json('POST', '/api/admin/config', [
            'config' => $this->getConfig()
        ])->assertOk();

        $this->actingAs($this->user, 'api')->json('GET', '/api/admin/config')
            ->assertOk()
            ->assertJsonFragment([ConfigEnum::CONFIG_KEY => $this->getAssertData()]);

        $this->json('GET', '/api/config')
            ->assertOk()
            ->assertJsonMissing([ConfigEnum::CONFIG_KEY]);
    }

    public function testAdministrableConfigApiInvalidData(): void
    {
        $this->actingAs($this->user, 'api')->json('POST', '/api/admin/config', [
            'config' => []
        ])->assertUnprocessable();

        $this->json('GET', '/api/config')
            ->assertOk()
            ->assertJsonMissing([ConfigEnum::CONFIG_KEY]);
    }

    private function getConfig(): array
    {
        $configKey = ConfigEnum::CONFIG_KEY;
        $h5p_show_display_option = false;
        $h5p_frame = false;
        $h5p_export = false;
        $h5p_embed = false;
        $h5p_copyright = false;
        $h5p_icon = false;
        $h5p_track_user = false;
        $h5p_ext_communication = false;
        $h5p_save_content_state = false;
        $h5p_save_content_frequency = 3;

        return [
            [
                'key' => "$configKey.h5p_show_display_option",
                'value' => $h5p_show_display_option,
            ],
            [
                'key' => "$configKey.h5p_frame",
                'value' => $h5p_frame,
            ],
            [
                'key' => "$configKey.h5p_export",
                'value' => $h5p_export,
            ],
            [
                'key' => "$configKey.h5p_embed",
                'value' => $h5p_embed,
            ],
            [
                'key' => "$configKey.h5p_copyright",
                'value' => $h5p_copyright,
            ],
            [
                'key' => "$configKey.h5p_icon",
                'value' => $h5p_icon,
            ],
            [
                'key' => "$configKey.h5p_track_user",
                'value' => $h5p_track_user,
            ],
            [
                'key' => "$configKey.h5p_ext_communication",
                'value' => $h5p_ext_communication,
            ],
            [
                'key' => "$configKey.h5p_save_content_state",
                'value' => $h5p_save_content_state,
            ],
            [
                'key' => "$configKey.h5p_save_content_frequency",
                'value' => $h5p_save_content_frequency,
            ]
        ];
    }

    private function getAssertData(): array
    {
        $configKey = ConfigEnum::CONFIG_KEY;

        return [
            'h5p_show_display_option' => [
                'full_key' => "$configKey.h5p_show_display_option",
                'key' => 'h5p_show_display_option',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_frame' => [
                'full_key' => "$configKey.h5p_frame",
                'key' => 'h5p_frame',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_export' => [
                'full_key' => "$configKey.h5p_export",
                'key' => 'h5p_export',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_embed' => [
                'full_key' => "$configKey.h5p_embed",
                'key' => 'h5p_embed',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_copyright' => [
                'full_key' => "$configKey.h5p_copyright",
                'key' => 'h5p_copyright',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_icon' => [
                'full_key' => "$configKey.h5p_icon",
                'key' => 'h5p_icon',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_track_user' => [
                'full_key' => "$configKey.h5p_track_user",
                'key' => 'h5p_track_user',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_ext_communication' => [
                'full_key' => "$configKey.h5p_ext_communication",
                'key' => 'h5p_ext_communication',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_save_content_state' => [
                'full_key' => "$configKey.h5p_save_content_state",
                'key' => 'h5p_save_content_state',
                'rules' => [
                    'boolean'
                ],
                'public' => false,
                'readonly' => false,
                'value' => false,
            ],
            'h5p_save_content_frequency' => [
                'full_key' => "$configKey.h5p_save_content_frequency",
                'key' => 'h5p_save_content_frequency',
                'rules' => [
                    'numeric'
                ],
                'public' => false,
                'readonly' => false,
                'value' => 3,
            ],
        ];
    }
}
