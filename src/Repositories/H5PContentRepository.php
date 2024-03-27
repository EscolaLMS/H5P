<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Repositories\Criteria\Criterion;
use EscolaLms\HeadlessH5P\Dtos\ContentFilterCriteriaDto;
use EscolaLms\HeadlessH5P\Exceptions\H5PException;
use EscolaLms\HeadlessH5P\Helpers\Helpers;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PTempFile;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Traits\QueryExtendable;
use H5PCore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


class H5PContentRepository implements H5PContentRepositoryContract
{
    use QueryExtendable;

    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function create(string $library, string $params, string $nonce): int
    {
        $user = auth()->user();
        $libNames = $this->hh5pService->getCore()->libraryFromString($library);
        $libDb = H5PLibrary::where([
            ['name', $libNames['machineName']],
            ['major_version', $libNames['majorVersion']],
            ['minor_version', $libNames['minorVersion']],
        ])->latest()->first();

        if ($libDb === null) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }

        $json = json_decode($params);

        if ($json === null) {
            throw new H5PException(H5PException::INVALID_PARAMETERS_JSON);
        }

        $content = $this->hh5pService->getCore()->saveContent([
            'library_id' => $libDb->id,
            'library' => $library,
            'parameters' => $params,
            'nonce' => $nonce,
            'user_id' => $user->getKey(),
            'author' => $user->email,
        ]);

        $this->filterParameters(H5PContent::findOrFail($content), $libDb);
        $this->moveTmpFilesToContentFolders($nonce, $content);

        return $content;
    }

    public function edit(int $id, string $library, string $params, string $nonce): int
    {
        $content = H5PContent::where('id', $id)->first();
        $libDb = H5PLibrary::where('id', $content->library_id)->first();

        if ($libDb === null) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }

        $json = json_decode($params);

        if ($json === null) {
            throw new H5PException(H5PException::INVALID_PARAMETERS_JSON);
        }

        if ($content === null) {
            throw new H5PException(H5PException::CONTENT_NOT_FOUND);
        }

        $id = $this->hh5pService->getCore()->saveContent([
            'id' => $id,
            'library_id' => $libDb->id,
            'library' => $library,
            'parameters' => $params,
            'filtered' => isset($json->params) ? json_encode($json->params) : $content['filtered']
        ], $id);

        $this->moveTmpFilesToContentFolders($nonce, $id);

        return $id;
    }

    private function moveTmpFilesToContentFolders($nonce, $contentId): bool
    {
        $storage_path = storage_path(config('hh5p.h5p_storage_path'));

        $files = H5PTempFile::where(['nonce' => $nonce])->get();

        foreach ($files as $file) {
            $old_path = $storage_path . $file->path;
            if (strpos($file->path, '/editor') !== false) {
                $new_path = $storage_path . str_replace('/editor', '/content/' . $contentId, $file->path);
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

    public function list(
        ContentFilterCriteriaDto $contentFilterDto,
                                 $per_page = 15,
        array                    $columns = ['hh5p_contents.*'],
        ?OrderDto                $orderDto = null,
    ): LengthAwarePaginator
    {
        $query = $this->getQueryContent($contentFilterDto, $columns);
        $query = $this->orderBy($query, $orderDto);
        $paginator = $query->paginate(intval($per_page));

        $paginator->getCollection()->transform(function ($content) {
            // Your code here
            if ($content->library) {
                $content->library->makeHidden(['semantics']);
                $content->library->setAppends([]);
            }

            return $content;
        });

        return $paginator;
    }

    public function unpaginatedList(
        ContentFilterCriteriaDto $contentFilterDto,
        array                    $columns = ['hh5p_contents.*'],
        ?OrderDto                $orderDto = null,
    ): Collection
    {
        $query = $this->getQueryContent($contentFilterDto, $columns);
        $query = $this->orderBy($query, $orderDto);
        $list = $query->get();

        $list->transform(function ($content) {
            // Your code here
            $content->library->makeHidden(['semantics']);
            $content->library->setAppends([]);

            return $content;
        });

        return $list;
    }

    public function delete(int $id): int
    {
        $content = H5PContent::findOrFail($id);
        $content->delete();

        $storage_path = storage_path(config('hh5p.h5p_content_storage_path') . $id);

        Helpers::deleteFileTree($storage_path);

        return $id;
    }

    public function show(int $id): H5PContent
    {
        return H5PContent::findOrFail($id);
    }

    public function upload($file, $content = null, $only_upgrade = null, $disable_h5p_security = false): H5PContent
    {
        if ($disable_h5p_security) {
            // Make it possible to disable file extension check
            $this->hh5pService->getCore()->disableFileCheck = (filter_input(INPUT_POST, 'h5p_disable_file_check', FILTER_VALIDATE_BOOLEAN) ? true : false);
        }

        $valid = $this->hh5pService->validatePackage($file, false, false);

        if ($valid) {
            $this->hh5pService->getRepository()->setMainData($this->hh5pService->getCore()->mainJsonData);
            $this->hh5pService->getStorage()->savePackage();
            // $this->hh5pService->getRepository()->deleteLibraryUsage($content['id']);
            $id = $this->hh5pService->getStorage()->contentId;

            $content = $this->hh5pService->getCore()->loadContent($id);

            $safe_parameters = $this->hh5pService->getCore()->filterParameters($content);

            return H5PContent::findOrFail($id);
        } else {
            @unlink($this->hh5pService->getRepository()->getUploadedH5pPath());
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }

        return false;

        // The uploaded file was not a valid H5P package
    }

    public function download($id): string
    {
        $content = $this->hh5pService->getCore()->loadContent($id);
        $content['filtered'] = '';
        $this->hh5pService->getCore()->filterParameters($content);

        $filename = $this->hh5pService->getRepository()->getDownloadFile($id);

        return storage_path('app/h5p/exports/' . $filename);
    }

    public function getLibraryById(int $id): H5PLibrary
    {
        return H5PLibrary::findOrFail($id);
    }

    public function deleteUnused(): Collection
    {
        $unused = $this->getUnused();

        foreach ($unused as $h5p) {
            $this->delete($h5p->getKey());
        }

        return $unused->pluck('id');
    }

    private function applyCriteria(Builder $query, array $criteria): Builder
    {
        foreach ($criteria as $criterion) {
            if ($criterion instanceof Criterion) {
                $query = $criterion->apply($query);
            }
        }

        return $query;
    }

    private function getQueryContent(ContentFilterCriteriaDto $contentFilterDto, array $columns = ['*']): Builder
    {
        $query = H5PContent::with(['library'])
            ->select($columns);

        $query = self::applyQueryJoin($query);
        $query = self::applyQuerySelect($query);
        $query = self::applyQueryGroupBy($query);

        return $this->applyCriteria($query, $contentFilterDto->toArray());
    }

    private function getUnused(): Collection
    {
        return H5PContent::query()
            ->whereRaw('(SELECT COUNT(*) FROM topic_h5ps WHERE hh5p_contents.id = topic_h5ps.value) <= 0')
            ->get();
    }

    private function filterParameters(H5PContent $h5pContent, H5PLibrary $h5pLibrary): void
    {
        $content = $h5pContent->toArray();
        $content['library'] = $h5pLibrary->toArray();
        $content['params'] = json_encode($content['params']);
        $content['metadata'] = json_decode(json_encode($content['metadata']), true);
        $content['slug'] = Str::slug($content['title']);
        $content['embedType'] = H5PCore::determineEmbedType($h5pContent->embededType ?? 'div', $h5pLibrary->embedTypes);

        $this->hh5pService->getCore()->filterParameters($content);
    }

    private function orderBy(Builder $query, ?OrderDto $dto): Builder
    {
        if ($dto) {
            match ($dto->getOrderBy()) {
                'library_title' => $query
                    ->withAggregate('library', 'title')
                    ->orderBy('library_title', $dto->getOrder() ?? 'asc'),
                'title' => $query->orderBy('parameters->metadata->title', $dto->getOrder() ?? 'asc'),
                default => $query->orderBy($dto->getOrderBy() ?? 'id', $dto->getOrder() ?? 'desc'),
            };
        }
        return $query;
    }
}
