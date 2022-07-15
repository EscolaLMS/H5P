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

        return $libraries_result;

        $versions = ['1.0.8', '1.0.9', '1.0.10'];
        usort($versions, 'version_compare');
        echo end($versions);

        /**
         *  global $wpdb;

    // Get latest version of local libraries
    $major_versions_sql =
      "SELECT hl.name,
                MAX(hl.major_version) AS major_version
           FROM {$wpdb->prefix}h5p_libraries hl
          WHERE hl.runnable = 1
       GROUP BY hl.name";

    $minor_versions_sql =
      "SELECT hl2.name,
                 hl2.major_version,
                 MAX(hl2.minor_version) AS minor_version
            FROM ({$major_versions_sql}) hl1
            JOIN {$wpdb->prefix}h5p_libraries hl2
              ON hl1.name = hl2.name
             AND hl1.major_version = hl2.major_version
        GROUP BY hl2.name, hl2.major_version";

    return $wpdb->get_results(
        "SELECT hl4.id,
                hl4.name AS machine_name,
                hl4.title,
                hl4.major_version,
                hl4.minor_version,
                hl4.patch_version,
                hl4.restricted,
                hl4.has_icon
           FROM ({$minor_versions_sql}) hl3
           JOIN {$wpdb->prefix}h5p_libraries hl4
             ON hl3.name = hl4.name
            AND hl3.major_version = hl4.major_version
            AND hl3.minor_version = hl4.minor_version");
         */
        // TODO 
        // Implement this 
        /*
        $recently_used = [];
        $result = DB::table('h5p_events')
            ->select([
                'library_name',
                'max(created_at) AS max_created_at',
            ])
            ->where('type', 'content')
            ->where('sub_type', 'create')
            ->where('user_id', Auth::id())
            ->groupBy('library_name')
            ->orderBy('max_created_at', 'DESC')
            ->get();

        foreach ($result as $row) {
            $recently_used[] = $row->library_name;
        }

        dd($recently_used);
        exit;

        return $recently_used;

        */
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
