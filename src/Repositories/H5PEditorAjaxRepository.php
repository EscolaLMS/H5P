<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use Exception;
use H5PEditorAjaxInterface;
use EscolaLms\HeadlessH5P\Models\H5pLibrariesHubCache;
use EscolaLms\HeadlessH5P\Helpers\Helpers;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

/**
 * Handles Ajax functionality that must be implemented separately for each of the
 * H5P plugins
 */
class H5PEditorAjaxRepository implements H5PEditorAjaxInterface
{
    /**
     * Gets latest library versions that exists locally
     *
     * @return array Latest version of all local libraries
     */
    public function getLatestLibraryVersions()
    {
        $all = H5PLibrary::where('runnable', 1)
            ->whereNotNull('semantics')
            ->orderBy('title', 'ASC')
            ->get();

        Helpers::fixCaseKeysArray(['majorVersion', 'minorVersion', 'patchVersion'], $all);

        $unique = $all
            ->groupBy('name')
            ->filter(fn($item) => $item->count() <= 1)
            ->flatten();

        $result = $all
            ->groupBy('name')
            ->filter(fn($item) => $item->count() > 1)
            ->map(fn($item) => $item
                ->sortBy('version', SORT_NATURAL)
                ->last()
            );


        return $unique->concat($result);
    }

    /**
     * Get locally stored Content Type Cache. If machine name is provided
     * it will only get the given content type from the cache
     *
     * @param $machineName
     *
     * @return array|object|null Returns results from querying the database
     */
    public function getContentTypeCache($machineName = null)
    {
        $libraries = H5pLibrariesHubCache::select();
        if ($machineName) {
            return $libraries->where('machine_name', $machineName)->pluck('id', 'is_recommended');
        } else {
            return $libraries->get();
        }
    }

    /**
     * Gets recently used libraries for the current author
     *
     * @return array machine names. The first element in the array is the
     * most recently used.
     */
    public function getAuthorsRecentlyUsedLibraries(): array
    {
        return H5PContent::query()
            ->join('hh5p_contents_libraries', 'hh5p_contents.id', '=', 'hh5p_contents_libraries.content_id')
            ->join('hh5p_libraries', 'hh5p_contents_libraries.library_id', '=', 'hh5p_libraries.id')
            ->groupBy('hh5p_libraries.name', 'hh5p_libraries.created_at')
            ->orderBy('hh5p_libraries.created_at')
            ->where('user_id', '=', auth()->user()->getKey())
            ->pluck('hh5p_libraries.name')
            ->toArray();
    }

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True if successful validation
     */
    public function validateEditorToken($token): bool
    {
        if (!$token) {
            return false;
        }

        $parser = app(Parser::class);
        $validator = app(Validator::class);

        try {
            $parsedToken = $parser->parse($token);
            $validator->assert($parsedToken, new SignedWith(new Sha256(), Key\InMemory::file(storage_path('oauth-public.key'))));

            return !$parsedToken->isExpired(now());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * Get translations for a language for a list of libraries
     *
     * @param array $libraries An array of libraries, in the form "<machineName> <majorVersion>.<minorVersion>
     * @param string $language_code
     * @return array
     */
    public function getTranslations($libraries, $language_code): array
    {
        $query = H5PLibrary::query()
            ->join('hh5p_libraries_languages', 'hh5p_libraries.id', '=', 'hh5p_libraries_languages.library_id');

        foreach ($libraries as $library) {
            $query->orWhere(
                fn($query) => $query
                    ->where('language_code', '=', $language_code)
                    ->where('name', '=', $library['name'] ?? null)
                    ->where('minor_version', '=', $library['minorVersion'] ?? null)
                    ->where('major_version', '=', $library['majorVersion'] ?? null)
            );
        }

        return $query
            ->get()
            ->pluck('translation', 'uberName')
            ->toArray();
    }
}
