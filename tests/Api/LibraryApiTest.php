<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class LibraryApiTest extends TestCase
{
    use DatabaseTransactions, H5PTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock->reset();
    }

    public function test_library_uploading(): void
    {
        $this->authenticateAsAdmin();
        $h5pFile = $this->getH5PFile();
        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        if ($response->status() >= 422) {
            echo $response->content();
        }

        $response->assertStatus(200);
    }

    public function test_library_index(): void
    {
        $this->authenticateAsAdmin();
        H5PLibrary::factory()->create();

        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/library');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [[
                'id',
                'machineName',
                'majorVersion',
                'minorVersion'
            ]]
        ]);
    }

    public function test_library_delete(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create();
        $id = $library->id;

        $response = $this->actingAs($this->user, 'api')->delete("/api/admin/hh5p/library/$id");
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'api')->delete("/api/admin/hh5p/library/$id");
        $response->assertStatus(404);
    }

    public function testGuestCannotDeleteLibrary(): void
    {
        $library = H5PLibrary::factory()->create();
        $id = $library->id;

        $response = $this->delete("/api/admin/hh5p/library/$id");

        $response->assertForbidden();
    }

    public function testGuestCanIndexLibrary(): void
    {
        $response = $this->get('/api/admin/hh5p/library');

        $response->assertOk();
    }

    public function testGuestCannotUploadLibrary(): void
    {
        $h5pFile = $this->getH5PFile();

        $response = $this->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertForbidden();
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

        $this->postJson('api/hh5p/library-install?_token=' . $token . '&id=H5P.ArithmeticQuiz-1.1' )
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
}
