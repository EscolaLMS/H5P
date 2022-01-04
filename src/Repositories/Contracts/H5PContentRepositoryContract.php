<?php
namespace EscolaLms\HeadlessH5P\Repositories\Contracts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Pagination\LengthAwarePaginator;

interface H5PContentRepositoryContract
{
    public function create(string $title, string $library, string $params, string $nonce): int;

    public function edit(int $id, string $title, string $library, string $params, string $nonce): int;

    public function list($per_page = 15, array $columns = ['*']): LengthAwarePaginator;

    public function unpaginatedList(array $columns = ['*']): Collection;

    public function delete(int $id): int;

    public function getLibraryById(int $id): H5PLibrary;
}
