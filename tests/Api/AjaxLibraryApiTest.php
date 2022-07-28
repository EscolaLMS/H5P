<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Http\Middleware\QueryToken;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class AjaxLibraryApiTest extends TestCase
{
    use DatabaseTransactions, H5PTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock->reset();
        Route::middleware([QueryToken::class, 'auth:api'])
            ->group(__DIR__ . './../../src/routes.php');
    }

    public function testGuestCannotUploadLibrary(): void
    {
        $h5pFile = $this->getH5PFile();

        $response = $this->postJson('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertUnauthorized();
    }

    public function testAjaxUploadLibraryGuestUser(): void
    {
        $this->postJson('api/hh5p/library-upload')->assertUnauthorized();
    }

    public function testAjaxUploadLibraryUser(): void
    {
        $this->authenticateAsUser();
        $token = $this->user->createToken("test")->accessToken;
        $this->postJson('api/hh5p/library-upload?_token=' . $token)->assertForbidden();
    }

    public function testAjaxUploadLibraryAdmin(): void
    {
        $this->mock->append(new Response(200, [], json_encode(["uuid" => "123"])));
        $this->mock->append(new Response(200, [], json_encode(["uuid" => "123"])));

        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $h5pFile = $this->getH5PFile();

        $response = $this->postJson('api/hh5p/library-upload?_token=' . $token . '&id=1&contentId=1', [
            'h5p' => $h5pFile,
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'h5p' => [
                        'title' => 'Arithmetic Quiz'
                    ]
                ]
            ]);
    }

    public function testAjaxInstallLibraryGuestUser(): void
    {
        $this->postJson('api/hh5p/library-install?id=H5P.Accordion')
            ->assertUnauthorized();
    }

    public function testAjaxInstallLibraryUser(): void
    {
        $this->authenticateAsUser();
        $token = $this->user->createToken("test")->accessToken;

        $this->postJson('api/hh5p/library-install?_token=' . $token . '&id=H5P.ArithmeticQuiz-1.1')
            ->assertForbidden();
    }

    public function testAjaxInstallLibraryAdmin(): void
    {
        $this->mock->append(new Response(200, ['Content-Type' => 'application/json'], $this->getH5PFile()));

        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $this->postJson('api/hh5p/library-install?_token=' . $token . '&id=H5P.ArithmeticQuiz-1.1')
            ->assertOk();
    }

    public function testAjaxInstallLibraryAdminLibraryNotFound(): void
    {
        $this->mock->append(new Response(404, []));

        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $this->postJson('api/hh5p/library-install?_token=' . $token . '&id=H5P.ArithmeticQuiz-1.1')
            ->assertStatus(500);
    }

    public function testAjaxGetLibrariesGuest(): void
    {
        $this->getJson('api/hh5p/libraries')
            ->assertUnauthorized();
    }

    public function testAjaxGetLibrariesUser(): void
    {
        $this->authenticateAsUser();
        $token = $this->user->createToken("test")->accessToken;

        $this->getJson('api/hh5p/libraries?_token=' . $token)
            ->assertForbidden();
    }

    public function testAjaxGetLibrariesAdmin(): void
    {
        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $this->getJson('api/hh5p/libraries?_token=' . $token)
            ->assertOk();
    }

    public function testAjaxPostLibrariesGuest(): void
    {
        $this->postJson('api/hh5p/libraries')
            ->assertUnauthorized();
    }

    public function testAjaxPostLibrariesUser(): void
    {
        $this->authenticateAsUser();
        $token = $this->user->createToken("test")->accessToken;

        $this->postJson('api/hh5p/libraries?_token=' . $token)
            ->assertForbidden();
    }

    public function testAjaxPostLibrariesAdmin(): void
    {
        $this->mock->append(new Response(200, ['Content-Type' => 'application/json'], $this->getH5PFile()));

        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $this->postJson('api/hh5p/libraries?_token=' . $token)
            ->assertOk();
    }
}
