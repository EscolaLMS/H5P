<?php

namespace EscolaLms\HeadlessH5P;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Policies\H5PContentPolicy;
use EscolaLms\HeadlessH5P\Policies\H5PLibraryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        H5PContent::class => H5PContentPolicy::class,
        H5PLibrary::class => H5PLibraryPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
