<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PContent;

class EditorApiTest extends TestCase
{
    public function test_editor_config_new()
    {
        $response = $this->get('/api/admin/hh5p/editor');

        $response->assertStatus(200);
        $data = json_decode($response->getContent());

        $this->assertTrue(isset($data->editor));
    }

    public function test_editor_config_content()
    {
        $data = [
            "library"=> "H5P.ArithmeticQuiz 1.1",
            "nonce"=>bin2hex(random_bytes(4)),
            "params"=> '{"params":{"quizType":"arithmetic","arithmeticType":"addition","equationType":"intermediate","useFractions":false,"maxQuestions":20,"UI":{"score":"Score:","time":"Time: @time","resultPageHeader":"Finished!","go":"GO!","startButton":"Start","retryButton":"Retry","correctText":"Correct","incorrectText":"Incorrect. Correct answer was :num","durationLabel":"Duration in hours, minutes and seconds.","humanizedQuestion":"What does :arithmetic equal?","humanizedEquation":"For the equation :equation, what does :item equal?","humanizedVariable":"What does :item equal?","plusOperator":"plus","subtractionOperator":"minus","multiplicationOperator":"times","divisionOperator":"delt på","equalitySign":"equals","slideOfTotal":"Slide :num of :total"},"intro":"Artimethic quiz"},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Artimethic quiz","title":"Artimethic quiz"}}',
            "title"=> "Artimethic quiz"
        ];

        $response = $this->postJson('/api/admin/hh5p/content', $data);

        $data = json_decode($response->getContent());

        $response = $this->get('/api/admin/hh5p/editor/'.$data->id);
        $response->assertStatus(200);

        $data = json_decode($response->getContent());

        $this->assertTrue(isset($data->editor));
    }
}
