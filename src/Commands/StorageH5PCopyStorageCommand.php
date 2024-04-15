<?php

namespace EscolaLms\HeadlessH5P\Commands;

use EscolaLms\HeadlessH5P\Repositories\H5PFileStorageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageH5PCopyStorageCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'h5p:storage-copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy local H5P storage to s3.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $link = Storage::path('h5p');
        $target = storage_path('app/h5p');

        app(H5PFileStorageRepository::class, ['path' => Storage::path('/') . '/h5p'])->copyVendorFiles($target, env('AWS_URL') . $link);

        $this->info("The files [$target] have been copied to [$link].");
    }
}
