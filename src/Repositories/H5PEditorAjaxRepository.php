<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use H5PEditorAjaxInterface;
use EscolaLms\HeadlessH5P\Models\H5pLibrariesHubCache;
use EscolaLms\HeadlessH5P\Helpers\Helpers;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

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


        $libraries_result = H5PLibrary::where('runnable', 1)
            ->whereNotNull('semantics')
            ->orderBy('title', 'ASC')
            ->get();

        Helpers::fixCaseKeysArray(['majorVersion', 'minorVersion', 'patchVersion'], $libraries_result);

        // TODO those libraries should be filtered by maximum sem ver 
        /*
        $versions = ['1.0.8', '1.0.9', '1.0.10'];
        usort($versions, 'version_compare');
        */



        return $libraries_result;
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
    public function getAuthorsRecentlyUsedLibraries()
    {
        // TODO implment this
        return [];
    }

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True if successful validation
     */
    public function validateEditorToken($token)
    {
        // TODO this is better resolved
        return true;
    }

    /**
     * Get translations for a language for a list of libraries
     *
     * @param array $libraries An array of libraries, in the form "<machineName> <majorVersion>.<minorVersion>
     * @param string $language_code
     * @return array
     */
    public function getTranslations($libraries, $language_code)
    {
    }
}
