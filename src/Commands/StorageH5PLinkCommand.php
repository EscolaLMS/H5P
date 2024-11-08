<?php

namespace EscolaLms\HeadlessH5P\Commands;

use EscolaLms\HeadlessH5P\Repositories\H5PFileStorageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\ExpectationFailedException;

class StorageH5PLinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'h5p:storage-link {--relative : Create the symbolic link using relative paths} {--overwrite : Overwrite files if they existed before}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the symbolic links for H5P configured for the application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $relative = $this->option('relative');
        $overwrite = $this->option('overwrite');

        $links = $this->links();

        foreach ($links as $link => $target) {
            if (!$overwrite) {
                if (Storage::directoryExists($link)) {
                    try {
                        Storage::assertDirectoryEmpty($link);
                    } catch (ExpectationFailedException $e) {
                        $this->error("The [$link] link already exists.");
                        continue;
                    }
                }
            }

            app(H5PFileStorageRepository::class, ['path' => env('AWS_URL')])->copyVendorFiles($target, $link);

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
            Storage::path('h5p-core') => base_path().'/vendor/h5p/h5p-core',
            Storage::path('h5p-editor') => base_path().'/vendor/h5p/h5p-editor',
        ];
    }
}
