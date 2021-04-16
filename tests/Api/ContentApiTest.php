<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class ContentApiTest extends TestCase
{
    public function test_library_create()
    {
        $library = H5PLibrary::first();

        $response = $this->postJson('/api/hh5p/content', [
            "title"=>"The Title",
            "library"=>$library->uberName,
            "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        if ($response->status() >= 400) {
            echo $response->content();
        }

        $response->assertStatus(200);
        $response->assertJsonStructure(['id']);
    }

    public function test_library_create_invalid_library()
    {
        $library = H5PLibrary::first();

        $response = $this->postJson('/api/hh5p/content', [
            "title"=>"The Title",
            "library"=>"Invalid lib name",
            "params"=>"{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        $response->assertStatus(400);
    }

    public function test_library_create_invalid_json()
    {
        $library = H5PLibrary::first();

        $response = $this->postJson('/api/hh5p/content', [
            "title"=>"The Title",
            "library"=>$library->uberName,
            "params"=>"XXX!!!{\"params\":{\"taskDescription\":\"Documentation tool\",\"pagesList\":[{\"params\":{\"elementList\":[{\"params\":{},\"library\":\"H5P.Text 1.1\",\"metadata\":{\"contentType\":\"Text\",\"license\":\"U\",\"title\":\"Untitled Text\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Text\"},\"subContentId\":\"da3387da-355a-49fb-92bc-3a9a4e4646a9\"}],\"helpTextLabel\":\"More information\",\"helpText\":\"\"},\"library\":\"H5P.StandardPage 1.5\",\"metadata\":{\"contentType\":\"Standard page\",\"license\":\"U\",\"title\":\"Untitled Standard page\",\"authors\":[],\"changes\":[],\"extraTitle\":\"Untitled Standard page\"},\"subContentId\":\"ac6ffdac-be02-448c-861c-969e6a09dbd5\"}],\"i10n\":{\"previousLabel\":\"poprzedni\",\"nextLabel\":\"Next\",\"closeLabel\":\"Close\"}},\"metadata\":{\"license\":\"U\",\"authors\":[],\"changes\":[],\"extraTitle\":\"fdsfds\",\"title\":\"fdsfds\"}}"
        ]);

        $response->assertStatus(400);
    }
}
