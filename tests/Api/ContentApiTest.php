<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Enums\H5PPermissionsEnum;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

class ContentApiTest extends TestCase
{
    use DatabaseTransactions, WithFaker, H5PTestingTrait;

    public function testContentCreate(): void
    {
        $this->authenticateAsAdmin();
        $h5pFile = $this->getH5PFile();

        $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/library', ['h5p_file' => $h5pFile,])
            ->assertOk();

        $library = H5PLibrary::where('runnable', 1)->first();

        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['id']]);

        $this->assertDatabaseHas('hh5p_contents', [
            'id' => $response->getData()->data->id,
            'user_id' => $this->user->id,
            'author' => $this->user->email
        ]);
    }

    public function testContentCreateNoNonce(): void
    {
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'library' => 'Invalid lib name',
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentCreateInvalidLibrary(): void
    {
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => 'Invalid lib name',
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentCreateInvalidJson(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);

        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => 'XXX!!!{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentUpdate(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $response = $this->actingAs($this->user, 'api')->postJson("/api/admin/hh5p/content/$id", [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id']]);
    }

    public function testContentUpdateNoNonce(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $response = $this->actingAs($this->user, 'api')->postJson("/api/admin/hh5p/content/$id", [
            'library' => 'Invalid lib name',
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentUpdateInvalidLibrary(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $response = $this->actingAs($this->user, 'api')->postJson("/api/admin/hh5p/content/$id", [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => 'Invalid lib name',
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentUpdateInvalidJson(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $response = $this->actingAs($this->user, 'api')->postJson("/api/admin/hh5p/content/$id", [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => 'XXX!!!{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentUpdateAuthor(): void
    {
        $this->authenticateAsUser();
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_AUTHOR_UPDATE);

        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'user_id' => $this->user->getKey(),
            'library_id' => $library->getKey()
        ]);
        $response = $this->actingAs($this->user, 'api')->postJson("/api/admin/hh5p/content/$content->id", [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ])->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id']]);
    }

    public function testContentCannotUpdateAuthor(): void
    {
        $this->authenticateAsUser();
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_AUTHOR_UPDATE);

        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'user_id' => 99999,
            'library_id' => $library->getKey()
        ]);
        $this->actingAs($this->user, 'api')->postJson("/api/admin/hh5p/content/$content->id")->assertForbidden();
    }

    public function testContentList(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $h5pContents = H5PContent::factory()
            ->count(10)
            ->create([
                'library_id' => $library->getKey()
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content');

        $this->assertContentListResponse($response);
        $data = $response->getData()->data;
        $this->assertEquals($h5pContents->last()->getKey(), $data[0]->id);
        $this->assertEquals($h5pContents->first()->getKey(), $data[count($data) - 1]->id);
    }

    public function testContentUnpaginatedList(): void
    {
        $h5pContents = H5PContent::factory()
            ->count(50)
            ->create(['library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey()]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content?per_page=0');

        $response
            ->assertOk()
            ->assertJsonCount(50, 'data')
            ->assertJsonStructure(['data' => [[
                'id',
                'created_at',
                'updated_at',
                'user_id',
                'author',
                'title',
                'library_id',
                'library' => [
                    'id',
                    'name',
                    'title',
                    'created_at',
                    'updated_at',
                    'machineName',
                    'uberName',
                    'libraryId',
                ],
                'slug',
                'filtered',
                'disable',
            ]]]);

        $data = $response->getData()->data;
        $this->assertEquals($h5pContents->last()->getKey(), $data[0]->id);
        $this->assertEquals($h5pContents->first()->getKey(), $data[count($data) - 1]->id);
    }

    public function testContentListFilterByAuthorId(): void
    {
        H5PContent::factory()
            ->count(10)
            ->create([
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey(),
                'user_id' => 9999
            ]);
        H5PContent::factory()
            ->count(5)
            ->create([
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey(),
                'user_id' => 5555
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content?author_id=' . 5555)
            ->assertOk()
            ->assertJson(['data' => [[
                'user_id' => 5555,
            ]]]);
        $this->assertContentListResponse($response, 5);
    }

    public function testAuthorShouldGetContentList(): void
    {
        $this->authenticateAsUser();
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_AUTHOR_LIST);

        H5PContent::factory()
            ->count(10)
            ->create([
                'user_id' => 99999,
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey()
            ]);
        H5PContent::factory()
            ->count(5)
            ->create([
                'user_id' => $this->user->getKey(),
                'author' => $this->user->email,
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey()
            ]);

        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content')
            ->assertOk()
            ->assertJson(['data' => [[
                'user_id' => $this->user->getKey(),
                'author' => $this->user->email
            ]]]);
        $this->assertContentListResponse($response, 5);
    }

    public function testAuthorShouldGetContentListAndNotAbleToFilterByAuthorId(): void
    {
        $this->authenticateAsUser();
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_AUTHOR_LIST);

        H5PContent::factory()
            ->count(10)
            ->create([
                'user_id' => 99999,
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey()
            ]);
        H5PContent::factory()
            ->count(5)
            ->create([
                'user_id' => $this->user->getKey(),
                'author' => $this->user->email,
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey()
            ]);

        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content?author_id=' . 99999)
            ->assertOk()
            ->assertJson(['data' => [[
                'user_id' => $this->user->getKey(),
                'author' => $this->user->email
            ]]]);
        $this->assertContentListResponse($response, 5);
    }

    public function testAuthorShouldGetEmptyListWhenNoCreateContent(): void
    {
        $this->authenticateAsUser();
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_AUTHOR_LIST);

        H5PContent::factory()
            ->count(10)
            ->create([
                'user_id' => 99999,
                'library_id' => H5PLibrary::factory()->create(['runnable' => 1])->getKey()
            ]);

        $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content')
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function testContentListPage(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        H5PContent::factory()
            ->count(30)
            ->create([
                'library_id' => $library->getKey()
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content?page=2&per_page=15');

        $this->assertContentListResponse($response, 15);
    }

    public function testContentListSearchTitle(): void
    {
        $title = $this->faker->word;
        $library = H5PLibrary::factory()
            ->create(['runnable' => 1]);
        H5PContent::factory()
            ->count(3)
            ->create([
                'library_id' => $library->getKey(),
                'parameters' => '{"params":{},"metadata":{"extraTitle":"' . $title . '","title":"' . $title . '"}}',
            ]);
        H5PContent::factory()
            ->create([
                'library_id' => $library->getKey()
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?title=' . $title);

        $this->assertContentListResponse($response, 3);
    }

    public function testContentListSearchLibraryId(): void
    {
        $library1 = H5PLibrary::factory()->create(['runnable' => 1]);
        $library2 = H5PLibrary::factory()->create(['runnable' => 1]);
        H5PContent::factory()
            ->count(2)
            ->create([
                'library_id' => $library1->getKey()
            ]);
        H5PContent::factory()
            ->create([
                'library_id' => $library2->getKey()
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content?library_id=' . $library1->getKey());

        $this->assertContentListResponse($response, 2);
    }

    public function testContentListPageSearchTitle(): void
    {
        $title = $this->faker->word;
        $library = H5PLibrary::factory()
            ->create(['runnable' => 1]);
        H5PContent::factory()
            ->count(30)
            ->create([
                'library_id' => $library->getKey(),
                'parameters' => '{"params":{},"metadata":{"extraTitle":"' . $title . '","title":"' . $title . '"}}',
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?page=2&per_page=20&title=' . $title);

        $this->assertContentListResponse($response);
    }

    public function testContentListPageSearchLibraryId(): void
    {
        $libraries = H5PLibrary::factory()
            ->count(2)
            ->create(['runnable' => 1]);
        $contents = H5PContent::factory()
            ->count(30)
            ->create([
                'library_id' => $libraries->first()->getKey()
            ]);

        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')
            ->get('/api/admin/hh5p/content?page=2&per_page=20&library_id=' . $contents->first()->library_id);

        $this->assertContentListResponse($response);
    }

    public function testContentDelete(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ])->assertOk();

        $response = $this->actingAs($this->user, 'api')->delete("/api/admin/hh5p/content/$id");
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'api')->delete("/api/admin/hh5p/content/$id");
        $response->assertStatus(404);
    }

    public function testContentShow(): void
    {
        $this->authenticateAsAdmin();
        $data = $this->uploadH5PFile();
        $id = $data['id'];

        $response = $this->actingAs($this->user, 'api')->get("/api/admin/hh5p/content/$id");
        $response->assertStatus(200);

        $data = json_decode($response->getContent());

        $cid = "cid-$id";

        $this->assertTrue(is_object($data->data->contents->$cid));
    }

    public function testContentShowNonExisting(): void
    {
        $this->authenticateAsAdmin();
        $id = 999999;
        $response = $this->actingAs($this->user, 'api')->get("/api/admin/hh5p/content/$id");
        $response->assertStatus(404);
    }

    public function testContentShowGuest(): void
    {
        $this->authenticateAsAdmin();
        $data = $this->uploadH5PFile();
        $uuid = $data['uuid'];

        $this->get("/api/hh5p/content/$uuid")
            ->assertStatus(200);
    }

    public function testContentShowInvalidUuidGuest(): void
    {
        $uuid = "UUID";
        $response = $this->get("/api/hh5p/content/$uuid");
        $response->assertStatus(422);
    }

    public function testContentShowNonExistingGuest(): void
    {
        $uuid = (string)Str::orderedUuid();
        $response = $this->get("/api/hh5p/content/$uuid");
        $response->assertStatus(404);
    }

    public function testContentUploading(): void
    {
        $this->authenticateAsAdmin();

        $h5pFile = $this->getH5PFile();

        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertStatus(200);

        $data = $response->getData()->data;
        $result = H5PContent::find($data->id);

        $this->assertTrue(is_integer($data->id));
        $this->assertTrue(is_object($data->params));
        $this->assertEquals('Arithmetic Quiz', $data->title);
        $this->assertNotEquals('New Content (from file)', $data->title);

        $this->assertEquals($data->uuid,$result->uuid);
        $this->assertEquals('Arithmetic Quiz', $result->title);
    }

    public function testContentExport(): void
    {
        $this->authenticateAsAdmin();
        $data = $this->uploadH5PFile();
        $id = $data['id'];

        $this->actingAs($this->user, 'api')
            ->get("/api/admin/hh5p/content/$id/export")
            ->assertStatus(200)
            ->assertDownload();
    }

    public function testContentExportByTokenInQuery(): void
    {
        $this->authenticateAsAdmin();
        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $data = $this->uploadH5PFile();
        $id = $data['id'];

        $this
            ->get("/api/admin/hh5p/content/$id/export?_token=$token")
            ->assertStatus(200)
            ->assertDownload();
    }

    public function testGuestCannotCreateContent(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);

        $response = $this->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertForbidden();
    }

    public function testGuestCannotUpdateContent(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $response = $this->postJson("/api/admin/hh5p/content/$id", [
            'nonce' => bin2hex(random_bytes(4)),
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertForbidden();
    }

    public function testGuestCannotListContent(): void
    {
        $response = $this->get('/api/admin/hh5p/content');

        $response->assertForbidden();
    }

    public function testGuestCannotShowContent(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;

        $response = $this->get("/api/admin/hh5p/content/$id");

        $response->assertForbidden();
    }

    public function testGuestCannotDeleteContent(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $id = $library->id;

        $response = $this->delete("/api/admin/hh5p/library/$id");

        $response->assertForbidden();
    }

    public function testShouldRemoveUnusedH5PWithFiles()
    {
        $this->authenticateAsAdmin();

        $response = $this->uploadH5PFile();
        $h5pFirstId = $response['id'];

        $response = $this->uploadH5PFile();
        $h5pSecondId = $response['id'];

        DB::table('topic_h5ps')->insert([
            'value' => $h5pFirstId,
        ]);

        $response = $this->delete('/api/admin/hh5p/unused');

        $response->assertOk();

        $this->assertDatabaseHas('hh5p_contents', [
            'id' => $h5pFirstId
        ]);
        $this->assertDatabaseMissing('hh5p_contents', [
            'id' => $h5pSecondId
        ]);

        $this->assertTrue(File::exists(storage_path('app/h5p/content/' . $h5pFirstId)));
        $this->assertFalse(File::exists(storage_path('app/h5p/content/' . $h5pSecondId)));
    }

    public function testShouldCreateUuidWhenIsEmptyAndWhenFetchContent(): void
    {
        $this->authenticateAsAdmin();

        $h5pContent = H5PContent::factory()->make(['uuid' => null, 'library_id' => 1]);
        $h5pContent->saveQuietly();

        $this->assertNull($h5pContent->uuid);

        $this->actingAs($this->user, 'api')->getJson("/api/admin/hh5p/content")->assertOk();
        $result = H5PContent::find($h5pContent->getKey());

        $this->assertNotNull($result->uuid);
    }

    public function testNotUpdateUUidWhenFetchContent(): void
    {
        $this->authenticateAsAdmin();
        $h5pContent = H5PContent::factory()->create(['library_id' => 1]);

        $this->assertNotNull($h5pContent->uuid);

        $this->actingAs($this->user, 'api')->getJson("/api/admin/hh5p/content")->assertOk();
        $result = H5PContent::find($h5pContent->getKey());

        $this->assertEquals($h5pContent->uuid, $result->uuid);
    }

    private function assertContentListResponse(TestResponse $response, int $dataCount = 10): void
    {
        $response
            ->assertOk()
            ->assertJsonCount($dataCount, 'data')
            ->assertJsonStructure(['meta' => [
                'current_page',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]])
            ->assertJsonStructure(['data' => [[
                'id',
                'created_at',
                'updated_at',
                'user_id',
                'author',
                'title',
                'library_id',
                'library' => [
                    'id',
                    'name',
                    'title',
                    'created_at',
                    'updated_at',
                    'machineName',
                    'uberName',
                    'libraryId',
                ],
                'slug',
                'filtered',
                'disable',
            ]]]);
    }
}
