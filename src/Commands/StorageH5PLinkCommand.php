<?php

namespace EscolaLms\HeadlessH5P\Commands;

use Illuminate\Console\Command;

class StorageH5PLinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'h5p-storage:link {--relative : Create the symbolic link using relative paths}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the symbolic links for H%p configured for the application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $relative = $this->option('relative');

        $links = $this->links();

        foreach ($links as $link => $target) {
            if (file_exists($link)) {
                $this->error("The [$link] link already exists.");
                continue;
            }

            if (is_link($link)) {
                $this->laravel->make('files')->delete($link);
            }

            if ($relative) {
                $this->laravel->make('files')->relativeLink($target, $link);
            } else {
                $this->laravel->make('files')->link($target, $link);
            }

            $this->info("The [$link] link has been connected to [$target].");
        }

        $this->info('The links have been created.');
    }

    /**
     * Get the symbolic links that are configured for the application.
     *
     * @return array
     */
    protected function links()
    {
        return[
            public_path('h5p') => storage_path('app/h5p'),
            public_path('h5p-core') => base_path().'vendor/h5p/h5p-core',
            public_path('h5p-editor') => base_path().'vendor/h5p/h5p-editor',
        ];
    }
}
