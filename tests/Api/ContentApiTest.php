<?php

namespace Tests\Feature;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Http\UploadedFile;

//use Illuminate\Support\Facades\App;

class ContentApiTest extends TestCase
{
    public function testContentCreate()
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $library = H5PLibrary::where('runnable', 1)->first();

        // TODO this should be from factory ?
        $response = $this->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'title' => 'The Title',
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id']);
    }

    public function testContentCreateNoNonce()
    {
        $response = $this->postJson('/api/admin/hh5p/content', [
            'title' => 'The Title',
            'library' => 'Invalid lib name',
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentCreateInvalidLibrary()
    {
        $response = $this->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'title' => 'The Title',
            'library' => 'Invalid lib name',
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    public function testContentCreateInvalidJson()
    {
        $library = H5PLibrary::where('runnable', 1)->first();

        $response = $this->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'title' => 'The Title',
            'library' => $library->uberName,
            'params' => 'XXX!!!{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $response->assertStatus(422);
    }

    ////

    public function testContentUpdate()
    {
        $content = H5PContent::first();
        $library = H5PLibrary::where('runnable', 1)->first();
        $id = $content->id;

        // TODO this should be from factory ?
        $response = $this->postJson("/api/admin/hh5p/content/$id", [
          'nonce' => bin2hex(random_bytes(4)),
          'title' => 'The Title',
          'library' => $library->uberName,
          'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
      ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id']);
    }

    public function testContentUpdateNoNonce()
    {
        $content = H5PContent::first();
        $id = $content->id;

        $response = $this->postJson("/api/admin/hh5p/content/$id", [
            'title' => 'The Title',
          'library' => 'Invalid lib name',
          'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
      ]);

        $response->assertStatus(422);
    }

    public function testContentUpdateInvalidLibrary()
    {
        $content = H5PContent::first();
        $id = $content->id;

        $response = $this->postJson("/api/admin/hh5p/content/$id", [
            'nonce' => bin2hex(random_bytes(4)),
          'title' => 'The Title',
          'library' => 'Invalid lib name',
          'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
      ]);

        $response->assertStatus(422);
    }

    public function testContentUpdateInvalidJson()
    {
        $library = H5PLibrary::where('runnable', 1)->first();
        $content = H5PContent::first();
        $id = $content->id;

        $response = $this->postJson("/api/admin/hh5p/content/$id", [
            'nonce' => bin2hex(random_bytes(4)),
          'title' => 'The Title',
          'library' => $library->uberName,
          'params' => 'XXX!!!{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
      ]);

        $response->assertStatus(422);
    }

    public function testContentList()
    {
        $response = $this->get('/api/admin/hh5p/content');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
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
        ]);
    }

    public function testContentListPage()
    {
        $response = $this->get('/api/admin/hh5p/content?page=2');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_page',
            'data',
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
        ]);
    }

    public function testContentDelete()
    {
        $library = H5PLibrary::where('runnable', 1)->first();

        // TODO this should be from factory ?
        $response = $this->postJson('/api/admin/hh5p/content', [
            'nonce' => bin2hex(random_bytes(4)),
            'title' => 'The Title',
            'library' => $library->uberName,
            'params' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
        ]);

        $content = H5PContent::latest()->first();

        $id = $content->id;

        $response = $this->delete("/api/admin/hh5p/content/$id");
        $response->assertStatus(200);

        $response = $this->delete("/api/admin/hh5p/content/$id");
        $response->assertStatus(422);
    }

    public function testContentShow()
    {
        $content = H5PContent::latest()->first();

        $id = $content->id;
        $response = $this->get("/api/admin/hh5p/content/$id");
        $response->assertStatus(200);

        $data = json_decode($response->getContent());

        $cid = "cid-$id";

        $this->assertTrue(is_object($data->contents->$cid));
    }

    public function testContentShowNonExisiting()
    {
        $id = 999999;
        $response = $this->get("/api/admin/hh5p/content/$id");
        $response->assertStatus(422);
    }

    public function testContentUploadig()
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->post('/api/admin/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertStatus(200);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_integer($data->id));
        $this->assertTrue(is_object($data->params));
    }

    public function testContentExport()
    {
        $content = H5PContent::latest()->first();

        $id = $content->id;
        $response = $this->get("/api/admin/hh5p/content/$id/export");
        $response->assertStatus(200);

        $response->assertDownload();
    }
}
