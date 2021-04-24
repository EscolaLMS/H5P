<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PTempFile;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Exceptions\H5PException;
use Illuminate\Pagination\LengthAwarePaginator;
use EscolaLms\HeadlessH5P\Helpers\Helpers;

class H5PContentRepository implements H5PContentRepositoryContract
{
    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function create(string $title, string $library, string $params, string $nonce):int
    {
        $libNames = $this->hh5pService->getCore()->libraryFromString($library);

        $libDb = H5PLibrary::where([
            ['name', $libNames['machineName']],
            ['major_version', $libNames['majorVersion']],
            ['minor_version', $libNames['minorVersion']],
        ])->first();

        if ($libDb === null) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }
        
        $json = json_decode($params);

        if ($json === null) {
            throw new H5PException(H5PException::INVALID_PARAMETERS_JSON);
        }

        $content = $this->hh5pService->getCore()->saveContent([
            'library_id'=> $libDb->id,
            'title'=>$title,
            'library'=>$library,
            'parameters'=>$params,
            'nonce'=>$nonce
        ]);

        $this->moveTmpFilesToContentFolders($nonce, $content);

        return $content;
    }

    public function edit(int $id, string $title, string $library, string $params, string $nonce):int
    {
        $libNames = $this->hh5pService->getCore()->libraryFromString($library);

        $libDb = H5PLibrary::where([
            ['name', $libNames['machineName']],
            ['major_version', $libNames['majorVersion']],
            ['minor_version', $libNames['minorVersion']],
        ])->first();

        if ($libDb === null) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }
        
        $json = json_decode($params);

        if ($json === null) {
            throw new H5PException(H5PException::INVALID_PARAMETERS_JSON);
        }

        $content = H5PLibrary::where('id', $id)->first();

        if ($content === null) {
            throw new H5PException(H5PException::CONTENT_NOT_FOUND);
        }

        $id = $this->hh5pService->getCore()->saveContent([
            'id'=>$id,
            'library_id'=> $libDb->id,
            'title'=>$title,
            'library'=>$library,
            'parameters'=>$params,
            //'nonce'=>$nonce
        ], $id);

    
        $this->moveTmpFilesToContentFolders($nonce, $id);

        return $id;
    }

    private function moveTmpFilesToContentFolders($nonce, $contentId):bool
    {

        // TODO: take this from config
        $storage_path = storage_path('app/h5p');

        $files = H5PTempFile::where(['nonce' => $nonce])->get();

        foreach ($files as $file) {
            $old_path = $storage_path.$file->path;
            if (strpos($file->path, '/editor') !== false) {
                $new_path = $storage_path.str_replace('/editor', '/content/'.$contentId, $file->path);
                $dir_path = dirname($new_path);
                if (!is_dir($dir_path)) {
                    mkdir($dir_path, 0777, true);
                }
                rename($old_path, $new_path);
            }

            $file->delete();
        }

        return true;
    }

    public function list($per_page = 15):LengthAwarePaginator
    {
        return H5PContent::with(['library'])->paginate(intval($per_page));
    }

    public function delete(int $id):int
    {
        $content = H5PContent::findOrFail($id);
        $content->delete();

        // TODO: take this from config
        $storage_path = storage_path("app/h5p/content/$id");

        Helpers::deleteFileTree($storage_path);

        return $id;
    }

    public function show(int $id):H5PContent
    {
        return H5PContent::findOrFail($id);
    }

    public function upload($file, $content = null, $only_upgrade = null, $disable_h5p_security = false):H5PContent
    {
        if ($disable_h5p_security) {
            // Make it possible to disable file extension check
            $this->hh5pService->getCore()->disableFileCheck = (filter_input(INPUT_POST, 'h5p_disable_file_check', FILTER_VALIDATE_BOOLEAN) ? true : false);
        }

        $valid = $this->hh5pService->validatePackage($file, false, false);

        if ($valid) {
            $this->hh5pService->getStorage()->savePackage();
            // $this->hh5pService->getRepository()->deleteLibraryUsage($content['id']);
            $id = $this->hh5pService->getStorage()->contentId;
            return H5PContent::findOrFail($id);
        } else {
            @unlink($this->hh5pService->getRepository()->getUploadedH5pPath());
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }

        return false;

        // The uploaded file was not a valid H5P package
    }
}
