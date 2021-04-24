<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PContent;

//use Illuminate\Support\Facades\App;

use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

class ContentApiTest extends TestCase
{
    public function test_content_create()
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->post('/api/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $library = H5PLibrary::first();

        // TODO this should be from factory ?
        $response = $this->postJson('/api/hh5p/content', [
            "nonce"=>bin2hex(random_bytes(4)),
            "title"=>"The Title",
            "library"=>$library->uberName,
            "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        if ($response->status() >= 422) {
            echo $response->content();
        }

        $response->assertStatus(200);
        $response->assertJsonStructure(['id']);
    }

    public function test_content_create_no_nonce()
    {
        $library = H5PLibrary::first();

        $response = $this->postJson('/api/hh5p/content', [
            "title"=>"The Title",
            "library"=>"Invalid lib name",
            "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        $response->assertStatus(422);
    }

    public function test_content_create_invalid_library()
    {
        $library = H5PLibrary::first();

        $response = $this->postJson('/api/hh5p/content', [
            "nonce"=>bin2hex(random_bytes(4)),
            "title"=>"The Title",
            "library"=>"Invalid lib name",
            "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        $response->assertStatus(422);
    }

    public function test_content_create_invalid_json()
    {
        $library = H5PLibrary::first();

        $response = $this->postJson('/api/hh5p/content', [
            "nonce"=>bin2hex(random_bytes(4)),
            "title"=>"The Title",
            "library"=>$library->uberName,
            "params"=>"XXX!!!{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        $response->assertStatus(422);
    }

    ////

    public function test_content_update()
    {
        $content = H5PContent::first();
        $library = H5PLibrary::first();
        $id = $content->id;

        // TODO this should be from factory ?
        $response = $this->postJson("/api/hh5p/content/$id", [
          "nonce"=>bin2hex(random_bytes(4)),
          "title"=>"The Title",
          "library"=>$library->uberName,
          "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
      ]);

        if ($response->status() >= 422) {
            echo $response->content();
        }

        $response->assertStatus(200);
        $response->assertJsonStructure(['id']);
    }

    public function test_content_update_no_nonce()
    {
        $content = H5PContent::first();
        $id = $content->id;

        $response = $this->postJson("/api/hh5p/content/$id", [
            "title"=>"The Title",
          "library"=>"Invalid lib name",
          "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
      ]);

        $response->assertStatus(422);
    }

    public function test_content_update_invalid_library()
    {
        $content = H5PContent::first();
        $id = $content->id;

        $response = $this->postJson("/api/hh5p/content/$id", [
            "nonce"=>bin2hex(random_bytes(4)),
          "title"=>"The Title",
          "library"=>"Invalid lib name",
          "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
      ]);

        $response->assertStatus(422);
    }

    public function test_content_update_invalid_json()
    {
        $library = H5PLibrary::first();
        $content = H5PContent::first();
        $id = $content->id;

        $response = $this->postJson("/api/hh5p/content/$id", [
            "nonce"=>bin2hex(random_bytes(4)),
          "title"=>"The Title",
          "library"=>$library->uberName,
          "params"=>"XXX!!!{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
      ]);

        $response->assertStatus(422);
    }

    public function test_content_list()
    {
        $response = $this->get("/api/hh5p/content");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "current_page",
            "data",
            "first_page_url",
            "from" ,
            "last_page" ,
            "last_page_url",
            "links",
            "next_page_url",
            "path",
            "per_page",
            "prev_page_url",
            "to",
            "total"
        ]);
    }

    public function test_content_list_page()
    {
        $response = $this->get("/api/hh5p/content?page=2");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "current_page",
            "data",
            "first_page_url",
            "from" ,
            "last_page" ,
            "last_page_url",
            "links",
            "next_page_url",
            "path",
            "per_page",
            "prev_page_url",
            "to",
            "total"
        ]);
    }

    
    public function test_content_delete()
    {
        $library = H5PLibrary::first();

        
        // TODO this should be from factory ?
        $response = $this->postJson('/api/hh5p/content', [
            "nonce"=>bin2hex(random_bytes(4)),
            "title"=>"The Title",
            "library"=>$library->uberName,
            "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        $content = H5PContent::latest()->first();

        $id = $content->id;
        
        $response = $this->delete("/api/hh5p/content/$id");
        $response->assertStatus(200);

        $response = $this->delete("/api/hh5p/content/$id");
        $response->assertStatus(422);
    }

    public function test_content_show()
    {
        $content = H5PContent::first();
        $id = $content->id;
        $response = $this->get("/api/hh5p/content/$id");
        $response->assertStatus(200);
       
        $data = json_decode($response->getContent());

        $cid ="cid-$id";

        $this->assertTrue(is_object($data->contents->$cid));
    }

    public function test_content_show_non_exisiting()
    {
        $id = 999999;
        $response = $this->get("/api/hh5p/content/$id");
        $response->assertStatus(422);
    }

    public function test_content_uploadig()
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->post('/api/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        if ($response->status() >= 422) {
            echo $response->content();
        }

        $response->assertStatus(200);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_integer($data->id));
        $this->assertTrue(is_object($data->params));
    }
}
