<?php

namespace EscolaLms\HeadlessH5P\Database\Factories;

use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentFactory extends Factory
{
    protected $model = H5PContent::class;

    public function definition()
    {
        $admin = User::role(UserRole::ADMIN)->inRandomOrder()->first();
        return [
            'user_id' => empty($admin) ? null : $admin->id,
            'title' => 'The Title',
            'parameters' => '{"params":{"taskDescription":"Documentation tool","pagesList":[{"params":{"elementList":[{"params":{},"library":"H5P.Text 1.1","metadata":{"contentType":"Text","license":"U","title":"Untitled Text","authors":[],"changes":[],"extraTitle":"Untitled Text"},"subContentId":"da3387da-355a-49fb-92bc-3a9a4e4646a9"}],"helpTextLabel":"More information","helpText":""},"library":"H5P.StandardPage 1.5","metadata":{"contentType":"Standard page","license":"U","title":"Untitled Standard page","authors":[],"changes":[],"extraTitle":"Untitled Standard page"},"subContentId":"ac6ffdac-be02-448c-861c-969e6a09dbd5"}],"i10n":{"previousLabel":"poprzedni","nextLabel":"Next","closeLabel":"Close"}},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"fdsfds","title":"fdsfds"}}',
            'nonce' => bin2hex(random_bytes(4)),
            'filtered' => 'filtered',
            'slug' => 'slug',
            'embed_type' => 'embed_type',
            'disable' => 0,
            'content_type' => 'content_type',
            'author' => 'author author',
            'license' => 'license',
            'keywords' => 'keywords',
            'description' => 'description',
        ];
    }
}
