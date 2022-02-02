<?php

namespace Tests\Feature;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContentApiTest extends TestCase
{
    use DatabaseTransactions;

    public function testContentCreate(): void
    {
        $this->authenticateAsAdmin();
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $library = H5PLibrary::where('runnable', 1)->first();

        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'title' => 'The Title',
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['id']]);
    }

    public function testContentCreateNoNonce(): void
    {
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/content', [
            'title' => 'The Title',
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
            'title' => 'The Title',
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
            'title' => 'The Title',
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
            'title' => 'The Title',
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
            'title' => 'The Title',
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
            'title' => 'The Title',
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
            'title' => 'The Title',
            'library' => $library->uberName,
            'params' => 'XXX!!!{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentList(): void
    {
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta' => [
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
        ]]);
    }

    public function testContentListPage(): void
    {
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?page=2');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'meta' => [
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
        ]]);
    }

    public function testContentListSearchTitle(): void
    {
        $count = H5PContent::where('title', 'LIKE', '%Title%')->count();
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?title=Title');

        $response->assertStatus(200);
        $response->assertJsonCount($count < 15 ? $count : 15, 'data');
    }

    public function testContentListSearchLibraryId(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $count = H5PContent::where('library_id', $library->getKey())->count();
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?library_id='.$content->library_id);

        $response->assertStatus(200);
        $response->assertJsonCount($count < 15 ? $count : 15, 'data');
    }

    public function testContentListPageSearchTitle(): void
    {
        // TODO factory

        $count = H5PContent::where('title', 'LIKE', '%Title%')->count();
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?page=2&title=Title');

        $response->assertStatus(200);
        $response->assertJsonCount($count > 15 ? ($count < 30 ? ($count-15) : 15) : 0, 'data');
    }

    public function testContentListPageSearchLibraryId(): void
    {
        // TODO factory

        $content = H5PContent::first();
        $count = H5PContent::where('library_id', $content->library_id)->count();
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/content?page=2&library_id='.$content->library_id);

        $response->assertStatus(200);
        $response->assertJsonCount($count > 15 ? ($count < 30 ? ($count-15) : 15) : 0, 'data');
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
            'title' => 'The Title',
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
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);
        $id = $content->id;
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

    public function testContentUploading(): void
    {
        $this->authenticateAsAdmin();
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertStatus(200);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_integer($data->data->id));
        $this->assertTrue(is_object($data->data->params));
    }

    public function testContentExport(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create(['runnable' => 1]);
        $content = H5PContent::factory()->create([
            'library_id' => $library->getKey()
        ]);

        $id = $content->id;
        $response = $this->actingAs($this->user, 'api')->get("/api/admin/hh5p/content/$id/export");
        $response->assertStatus(200);

        $response->assertDownload();
    }

    public function testGuestCannotCreateContent(): void
    {
        $library = H5PLibrary::factory()->create(['runnable' => 1]);

        $response = $this->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'title' => 'The Title',
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
            'title' => 'The Title',
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
        $content = H5PContent::latest()->first();
        $id = $content->id;

        $response = $this->get("/api/admin/hh5p/content/$id");

        $response->assertForbidden();
    }

    public function testGuestCannotDeleteContent(): void
    {
        $library = H5PLibrary::where('runnable', 1)->first();
        $id = $library->id;

        $response = $this->delete("/api/admin/hh5p/library/$id");

        $response->assertForbidden();
    }

    public function testShouldRemoveUnusedH5PWithFiles()
    {
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

    private function uploadH5PFile(): array
    {
        $this->authenticateAsAdmin();
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);
        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);
        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        return $response->json('data');
    }
}
