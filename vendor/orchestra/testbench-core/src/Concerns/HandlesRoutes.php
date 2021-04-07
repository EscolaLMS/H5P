<?php

namespace Orchestra\Testbench\Concerns;

trait HandlesRoutes
{
    /**
     * Setup routes requirements.
     */
    protected function setUpApplicationRoutes(): void
    {
        if ($this->app->eventsAreCached()) {
            return;
        }

        $this->defineRoutes($this->app['router']);

        $this->app['router']->middleware('web')
            ->group(function ($router) {
                $this->defineWebRoutes($router);
            });

        if (\method_exists($this, 'parseTestMethodAnnotations')) {
            $this->parseTestMethodAnnotations($this->app, 'define-route');
        }

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    protected function defineRoutes($router)
    {
        // Define routes.
    }

    /**
     * Define web routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    protected function defineWebRoutes($router)
    {
        // Define routes.
    }

    /**
     * Define cache routes setup.
     *
     * @param  string  $route
     *
     * @return void
     */
    protected function defineCacheRoutes(string $route)
    {
        $files = $this->app['files'];

        $files->put(
            \base_path('routes/testbench.php'), $route
        );

        $this->artisan('route:cache')->run();

        $this->reloadApplication();

        $this->assertTrue(
            $files->exists(\base_path('bootstrap/cache/routes-v7.php'))
        );

        $this->requireApplicationCachedRoutes();

        $this->beforeApplicationDestroyed(function () use ($files) {
            $files->delete(
                \base_path('bootstrap/cache/routes-v7.php'),
                \base_path('routes/testbench.php')
            );
        });
    }

    /**
     * Require application cached routes.
     */
    protected function requireApplicationCachedRoutes(): void
    {
        $this->app->booted(function () {
            require $this->app->getCachedRoutesPath();
        });
    }
}
