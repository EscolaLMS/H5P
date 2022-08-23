<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use EscolaLms\HeadlessH5P\Exceptions\H5PException;
use EscolaLms\HeadlessH5P\Helpers\Helpers;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PContentLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;
use EscolaLms\HeadlessH5P\Models\H5pLibrariesHubCache;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PFrameworkInterface;
use H5PPermission;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Facades\Cache;

class H5PRepository implements H5PFrameworkInterface
{
    protected $messages = ['error' => [], 'updated' => []];

    private array $downloadFiles;

    private array $mainData;

    public function setMainData(array $mainData): void
    {
        $this->mainData = $mainData;
    }

    /**
     * Returns info for the current platform.
     *
     * @return array
     *               An associative array containing:
     *               - name: The name of the platform, for instance "Wordpress"
     *               - version: The version of the platform, for instance "4.0"
     *               - h5pVersion: The version of the H5P plugin/module
     */
    public function getPlatformInfo()
    {
        return array(
            'name' => 'Wellms.io',
            'version' => '0.1.0',
            'h5pVersion' => '0.1.0',
        );
    }

    /**
     * Fetches a file from a remote server using HTTP GET.
     *
     * @param string $url      where you want to get or send data
     * @param array  $data     data to post to the URL
     * @param bool   $blocking set to 'FALSE' to instantly time out (fire and forget)
     * @param string $stream   path to where the file should be saved
     * @param bool   $fullData Return additional response data such as headers and potentially other data
     * @param array  $headers  Headers to send
     * @param array  $files    Files to send
     * @param string $method
     *
     * @return string|array The content (response body), or an array with data. NULL if something went wrong
     */
    public function fetchExternalData($url, $data = null, $blocking = true, $stream = null, $fullData = false, $headers = [], $files = [], $method = 'POST')
    {
        @set_time_limit(0);
        $options = [
            'timeout'  => !empty($blocking) ? 30 : 0.01,
        ];

        if (!empty($stream)) {
            $options['sink'] = $stream;
        }

        $client = new Client(config('hh5p.guzzle'));
        try {
            if ($data !== null) {
                // Post
                $options['form_params'] = $data;
                $response = $client->request('POST', $url, $options);
            } else {
                $response = $client->request('GET', $url, $options);
            }

            if ($response->getStatusCode() === 200) {
                $body = empty($response->getBody()) ? null : $response->getBody()->getContents();

                if ($body) {
                    return $fullData ? ['status' => $response->getStatusCode(), 'data' => json_decode($body)] : $body;
                }

                return true;
            } else {
                return false;
            }
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Set the tutorial URL for a library. All versions of the library is set.
     *
     * @param string $machineName
     * @param string $tutorialUrl
     */
    public function setLibraryTutorialUrl($machineName, $tutorialUrl)
    {
    }

    /**
     * Show the user an error message.
     *
     * @param string $message The error message
     * @param string $code    An optional code
     */
    public function setErrorMessage($message, $code = null)
    {
        $this->messages['error'][] = $message;
    }

    /**
     * Show the user an information message.
     *
     * @param string $message
     *                        The error message
     */
    public function setInfoMessage($message)
    {
        $this->messages['updated'][] = $message;
    }

    /**
     * Return messages.
     *
     * @param string $type 'info' or 'error'
     *
     * @return string[]
     */
    public function getMessages($type = 'error')
    {
        return !empty($this->messages[$type]) ? $this->messages[$type] : null;
    }

    /**
     * Translation function.
     *
     * @param string $message
     *                             The english string to be translated
     * @param array  $replacements
     *                             An associative array of replacements to make after translation. Incidences
     *                             of any key in this array are replaced with the corresponding value. Based
     *                             on the first character of the key, the value is escaped and/or themed:
     *                             - !variable: inserted as is
     *                             - @variable: escape plain text to HTML
     *                             - %variable: escape text and theme as a placeholder for user-submitted
     *                             content
     *
     * @return string Translated string
     *                Translated string
     */
    public function t($message, $replacements = []): string
    {
        // Insert !var as is, escape @var and emphasis %var.
        foreach ($replacements as $key => $replacement) {
            if ($key[0] === '@') {
                // $replacements[$key] = esc_html($replacement);
                $replacements[$key] = $replacement;
            } elseif ($key[0] === '%') {
                // $replacements[$key] = '<em>' . esc_html($replacement) . '</em>';
                $replacements[$key] = '<em>' . $replacement . '</em>';
            }
        }
        $message = preg_replace('/(!|@|%)[a-z0-9]+/i', '%s', $message);

        return vsprintf(__($message), $replacements);
    }

    /**
     * Get URL to file in the specific library.
     *
     * @param string $libraryFolderName
     * @param string $fileName
     *
     * @return string URL to file
     */
    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {
        $path = '/h5p/libraries/' . $libraryFolderName . '/' . $fileName;
        return file_exists(storage_path($path)) ? env('APP_URL') . $path : null;
    }

    /**
     * Get the Path to the last uploaded h5p.
     *
     * @return string
     *                Path to the folder where the last uploaded h5p for this session is located
     */
    public function getUploadedH5pFolderPath()
    {
        static $dir; // such a stupid way to have singleton ....
        if (is_null($dir)) {
            $dir = storage_path('app/h5p/temp/temp/') . uniqid('h5p-');
            @mkdir(dirname($dir), 0777, true);
        }

        return $dir;
    }

    /**
     * Get the path to the last uploaded h5p file.
     *
     * @return string
     *                Path to the last uploaded h5p
     */
    public function getUploadedH5pPath()
    {
        static $path; // such a stupid way to have singleton ....
        if (is_null($path)) {
            $path = storage_path('app/h5p/temp/temp/') . uniqid('h5p-') . '.h5p';
            @mkdir(dirname($path), 0777, true);
        }

        return $path;
    }

    /**
     * Load addon libraries.
     *
     * @return array
     */
    public function loadAddons(): array
    {
        return H5PLibrary::query()
            ->select(['l1.id', 'l1.name', 'l1.major_version', 'l1.minor_version', 'l1.patch_version', 'l1.preloaded_js', 'l1.preloaded_css', 'l1.add_to'])
            ->from('hh5p_libraries as l1')
            ->leftJoin('hh5p_libraries as l2', fn($join) => $join
                ->on('l1.name', '=', 'l2.name')
                ->on(fn($query) => $query
                    ->on('l1.major_version', '<', 'l2.major_version')
                    ->orOn(fn ($query) => $query
                        ->orOn('l1.major_version', '=', 'l2.major_version')
                        ->on('l1.minor_version', '<', 'l2.minor_version')
                    )
                )
            )
            ->whereNotNull('l1.add_to')
            ->whereNull('l2.name')
            ->get()
            ->toArray();
    }

    /**
     * Load config for libraries.
     *
     * @param array $libraries
     *
     * @return array
     */
    public function getLibraryConfig($libraries = null)
    {
    }

    /**
     * Get a list of the current installed libraries.
     *
     * @return array
     *               Associative array containing one entry per machine name.
     *               For each machineName there is a list of libraries(with different versions)
     */
    public function loadLibraries()
    {
        $results = H5pLibrary::select([
            'id',
            'name',
            'title',
            'major_version',
            'minor_version',
            'patch_version',
            'runnable',
            'restricted',
        ])
            ->orderBy('title', 'ASC')
            ->orderBy('major_version', 'ASC')
            ->orderBy('minor_version', 'ASC')
            ->get();

        $libraries = [];
        foreach ($results as $library) {
            $libraries[$library->name][] = $library;
        }

        return $libraries;
    }

    /**
     * Returns the URL to the library admin page.
     *
     * @return string
     *                URL to admin page
     */
    public function getAdminUrl()
    {
    }

    /**
     * Get id to an existing library.
     * If version number is not specified, the newest version will be returned.
     *
     * @param string $machineName
     *                             The librarys machine name
     * @param int    $majorVersion
     *                             Optional major version number for library
     * @param int    $minorVersion
     *                             Optional minor version number for library
     *
     * @return int
     *             The id of the specified library or FALSE
     */
    public function getLibraryId($machineName, $majorVersion = null, $minorVersion = null)
    {
        $where = H5PLibrary::where('name', $machineName);

        if ($majorVersion !== null) {
            $where->where('major_version', $majorVersion);
            if ($minorVersion !== null) {
                $where->where('minor_version', $minorVersion);
            }
        }

        $return = $where->orderBy('major_version', 'DESC')
            ->orderBy('minor_version', 'DESC')
            ->orderBy('patch_version', 'DESC')
            ->first();

        return $return === null ? false : $return->id;
    }

    /**
     * Get file extension whitelist.
     *
     * The default extension list is part of h5p, but admins should be allowed to modify it
     *
     * @param bool   $isLibrary
     *                                        TRUE if this is the whitelist for a library. FALSE if it is the whitelist
     *                                        for the content folder we are getting
     * @param string $defaultContentWhitelist
     *                                        A string of file extensions separated by whitespace
     * @param string $defaultLibraryWhitelist
     *                                        A string of file extensions separated by whitespace
     */
    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
    {
        $whitelist = $defaultContentWhitelist;
        if ($isLibrary) {
            $whitelist .= ' ' . $defaultLibraryWhitelist;
        }

        return $whitelist;
    }

    /**
     * Is the library a patched version of an existing library?
     *
     * @param object $library
     *                        An associative array containing:
     *                        - machineName: The library machineName
     *                        - majorVersion: The librarys majorVersion
     *                        - minorVersion: The librarys minorVersion
     *                        - patchVersion: The librarys patchVersion
     *
     * @return bool
     *              TRUE if the library is a patched version of an existing library
     *              FALSE otherwise
     */
    public function isPatchedLibrary($library)
    {
        return true;
    }

    /**
     * Is H5P in development mode?
     *
     * @return bool
     *              TRUE if H5P development mode is active
     *              FALSE otherwise
     */
    public function isInDevMode()
    {
        return true;
    }

    /**
     * Is the current user allowed to update libraries?
     *
     * @return bool
     *              TRUE if the user is allowed to update libraries
     *              FALSE if the user is not allowed to update libraries
     */
    public function mayUpdateLibraries()
    {
        return true;
    }

    /**
     * Convert list of file paths to csv.
     *
     * @param array  $library
     *                        Library data as found in library.json files
     * @param string $key
     *                        Key that should be found in $libraryData
     *
     * @return string
     *                file paths separated by ', '
     */
    private function pathsToCsv($library, $key)
    {
        if (isset($library[$key])) {
            $paths = [];
            foreach ($library[$key] as $file) {
                $paths[] = $file['path'];
            }

            return implode(', ', $paths);
        }

        return '';
    }

    /**
     * Store data about a library.
     *
     * Also fills in the libraryId in the libraryData object if the object is new
     *
     * @param object $libraryData
     *                            Associative array containing:
     *                            - libraryId: The id of the library if it is an existing library.
     *                            - title: The library's name
     *                            - machineName: The library machineName
     *                            - majorVersion: The library's majorVersion
     *                            - minorVersion: The library's minorVersion
     *                            - patchVersion: The library's patchVersion
     *                            - runnable: 1 if the library is a content type, 0 otherwise
     *                            - metadataSettings: Associative array containing:
     *                            - disable: 1 if the library should not support setting metadata (copyright etc)
     *                            - disableExtraTitleField: 1 if the library don't need the extra title field
     *                            - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *                            - embedTypes(optional): list of supported embed types
     *                            - preloadedJs(optional): list of associative arrays containing:
     *                            - path: path to a js file relative to the library root folder
     *                            - preloadedCss(optional): list of associative arrays containing:
     *                            - path: path to css file relative to the library root folder
     *                            - dropLibraryCss(optional): list of associative arrays containing:
     *                            - machineName: machine name for the librarys that are to drop their css
     *                            - semantics(optional): Json describing the content structure for the library
     *                            - language(optional): associative array containing:
     *                            - languageCode: Translation in json format
     * @param bool   $new
     *
     * @return
     */
    public function saveLibraryData(&$libraryData, $new = true)
    {
        $library = [
            'name' => $libraryData['machineName'],
            'title' => $libraryData['title'],
            'major_version' => $libraryData['majorVersion'],
            'minor_version' => $libraryData['minorVersion'],
            'patch_version' => $libraryData['patchVersion'],
            'runnable' => $libraryData['runnable'],
            'fullscreen' => isset($libraryData['fullscreen']) ? $libraryData['fullscreen'] : 0,
            'embed_types' => isset($libraryData['embedTypes']) ? implode(', ', $libraryData['embedTypes']) : '',
            'preloaded_js' => $this->pathsToCsv($libraryData, 'preloadedJs'),
            'preloaded_css' => $this->pathsToCsv($libraryData, 'preloadedCss'),
            'drop_library_css' => '', // TODO, what is this ?
            'semantics' => isset($libraryData['semantics']) ? $libraryData['semantics'] : '',
            'tutorial_url' => isset($libraryData['tutorial_url']) ?: '',
            'has_icon' => isset($libraryData['hasIcon']) ? 1 : 0,
            'add_to' => isset($library['addTo']) ? json_encode($library['addTo']) : null
        ];

        $libObj = H5PLibrary::firstOrCreate($library);

        $library['libraryId'] = $libObj->id;
        $this->deleteLibraryDependencies($library['libraryId']);

        if (isset($libraryData['language'])) {
            $languages = [];

            foreach ($libraryData['language'] as $languageCode => $translation) {
                $translation = [
                    'library_id' => $library['libraryId'],
                    'language_code' => $languageCode,
                    'translation' => $translation,
                ];
                $languages[] = $translation;
                H5PLibraryLanguage::firstOrCreate($translation);
            }
        }

        // This is essential, as this method should mutate the `libraryData`
        // I hate mutations

        $libraryData = array_merge($libraryData, $library);
    }

    /**
     * Insert new content.
     *
     * @param array $content
     *                             An associative array containing:
     *                             - id: The content id
     *                             - params: The content in json format
     *                             - library: An associative array containing:
     *                             - libraryId: The id of the main library for this content
     * @param int   $contentMainId
     *                             Main id for the content if this is a system that supports versions
     */
    public function insertContent($content, $contentMainId = null)
    {
        return $this->updateContent($content, $contentMainId);
    }

    private function fixContentParamsMetadataLibraryTitle($content)
    {
        $defaultTitle = isset($this->mainData['title']) ? $this->mainData['title'] : 'New Content (from file)';

        if (is_array($content['library'])) {
            $content['library_id'] = isset($content['library']['libraryId']) ? $content['library']['libraryId'] : $content['library']['id'];
            $lib = $this->loadLibrary($content['library']['machineName'], $content['library']['majorVersion'], $content['library']['minorVersion']);
            $content['embed_type'] = $lib['embed_types'];
        }

        // `parameters` is string, encode
        if (isset($content['parameters']) && is_string($content['parameters'])) {
            $parameters = json_decode($content['parameters']);
            if (is_object($parameters) && isset($parameters->params)) {
                $parameters = $parameters->params;
            }
            if (is_object($parameters) && isset($parameters->metadata)) {
                $metadata = $parameters->metadata;
            }
            if (is_array($parameters) && isset($parameters['params'])) {
                $parameters = $parameters['params'];
            }
            if (is_array($parameters) && isset($parameters['metadata'])) {
                $metadata = $parameters['metadata'];
            }
        }

        // `params` is string, encode
        if (!isset($parameters) && is_string($content['params'])) {
            $parameters = json_decode($content['params']);
            if (is_object($parameters) && isset($parameters->params)) {
                $parameters = $parameters->params;
            }
            if (is_object($parameters) && isset($parameters->metadata)) {
                $metadata = $parameters->metadata;
            }
            if (is_array($parameters) && isset($parameters['params'])) {
                $parameters = $parameters['params'];
            }
            if (is_array($parameters) && isset($parameters['metadata'])) {
                $metadata = $parameters['metadata'];
            }
        }
        // `params is array
        if (!isset($parameters) && is_array($content['params'])) {
            if (is_array($content['params']['params'])) {
                $parameters = $parameters['params']['params'];
            } else {
                $parameters = $parameters['params'];
            }
        }

        if (!isset($content['nonce'])) {
            $content['nonce'] = bin2hex(random_bytes(4));
        }

        if (!isset($content['title'])) {
            $content['title'] = $defaultTitle;
        }

        if (!isset($metadata)) {
            $metadata = ['license' => 'U', 'authors' => [], 'changes' => [], 'extraTitle' => $defaultTitle, 'title' => $defaultTitle];
        }

        $parameters = [
            'params' => $parameters,
            'metadata' => $metadata,
        ];

        $content['parameters'] = json_encode($parameters);

        return $content;
    }

    /**
     * Update old content.
     *
     * @param array $content
     *                             An associative array containing:
     *                             - id: The content id
     *                             - params: The content in json format
     *                             - library: An associative array containing:
     *                             - libraryId: The id of the main library for this content
     * @param int   $contentMainId
     *                             Main id for the content if this is a system that supports versions
     */
    public function updateContent($content, $contentMainId = null)
    {
        $content = $this->fixContentParamsMetadataLibraryTitle($content);
        if (isset($content['id'])) {
            H5PContent::findOrFail($content['id'])->update($content);

            return $content['id'];
        } else {
            unset($content['params']);
            unset($content['library']);

            $newContent = H5PContent::create($content);
            return $newContent->id;
        }
    }

    /**
     * Resets marked user data for the given content.
     *
     * @param int $contentId
     */
    public function resetContentUserData($contentId)
    {
    }

    /**
     * Save what libraries a library is depending on.
     *
     * @param int    $libraryId
     *                                Library Id for the library we're saving dependencies for
     * @param array  $dependencies
     *                                List of dependencies as associative arrays containing:
     *                                - machineName: The library machineName
     *                                - majorVersion: The library's majorVersion
     *                                - minorVersion: The library's minorVersion
     * @param string $dependency_type
     *                                What type of dependency this is, the following values are allowed:
     *                                - editor
     *                                - preloaded
     *                                - dynamic
     */
    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type)
    {
        foreach ($dependencies as $dependency) {
            $dbLib = H5PLibrary::where([
                'name' => $dependency['machineName'],
                'major_version' => $dependency['majorVersion'],
                'minor_version' => $dependency['minorVersion'],
            ])->firstOrFail();

            H5PLibraryDependency::firstOrCreate([
                'library_id' => $libraryId,
                'required_library_id' => $dbLib->id,
                'dependency_type' => $dependency_type,
            ]);
        }

        return true;
    }

    /**
     * Give an H5P the same library dependencies as a given H5P.
     *
     * @param int $contentId
     *                           Id identifying the content
     * @param int $copyFromId
     *                           Id identifying the content to be copied
     * @param int $contentMainId
     *                           Main id for the content, typically used in frameworks
     *                           That supports versions. (In this case the content id will typically be
     *                           the version id, and the contentMainId will be the frameworks content id
     */
    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = null)
    {

    }

    /**
     * Deletes content data.
     *
     * @param int $contentId
     *                       Id identifying the content
     */
    public function deleteContentData($contentId)
    {
    }

    /**
     * Delete what libraries a content item is using.
     *
     * @param int $contentId
     *                       Content Id of the content we'll be deleting library usage for
     */
    public function deleteLibraryUsage($contentId)
    {
        H5PContent::findOrFail($contentId)->libraries()->delete();
    }

    /**
     * Saves what libraries the content uses.
     *
     * @param int   $contentId
     *                              Id identifying the content
     * @param array $librariesInUse
     *                              List of libraries the content uses. Libraries consist of associative arrays with:
     *                              - library: Associative array containing:
     *                              - dropLibraryCss(optional): comma separated list of machineNames
     *                              - machineName: Machine name for the library
     *                              - libraryId: Id of the library
     *                              - type: The dependency type. Allowed values:
     *                              - editor
     *                              - dynamic
     *                              - preloaded
     */
    public function saveLibraryUsage($contentId, $librariesInUse)
    {
        $contentLibraries = array_map(function ($value) use ($contentId) {
            $contentLibrary = H5PContentLibrary::query()
                ->where([
                    'content_id' => $contentId,
                    'library_id' => $value['library']['id'],
                    'dependency_type' => $value['type'],
                ])
                ->first();
            if (!$contentLibrary) {
                $contentLibrary = new H5PContentLibrary([
                    'content_id' => $contentId,
                    'library_id' => $value['library']['id'],
                    'dependency_type' => $value['type'],
                    'drop_css' => boolval($value['library']['dropLibraryCss']),
                    'weight' => $value['weight'],
                ]);
                $contentLibrary->save();
            }
            return $contentLibrary->toArray();
        }, $librariesInUse);

        $content = H5PContent::with('library')->findOrFail($contentId);

        $libraryLibraries = array_map(function ($value) use ($contentId) {
            return H5PContentLibrary::firstOrCreate([
                'content_id' => $contentId,
                'library_id' => $value['required_library_id'],
                'dependency_type' => $value['dependencyType'],
            ], [
                'drop_css' => false,
                'weight' => 0,
            ])->toArray();
        }, $content->library->dependencies->toArray());
    }

    /**
     * Get number of content/nodes using a library, and the number of
     * dependencies to other libraries.
     *
     * @param int  $libraryId
     *                          Library identifier
     * @param bool $skipContent
     *                          Flag to indicate if content usage should be skipped
     *
     * @return array
     *               Associative array containing:
     *               - content: Number of content using the library
     *               - libraries: Number of libraries depending on the library
     */
    public function getLibraryUsage($libraryId, $skipContent = false)
    {
        $contentsCount = $skipContent ? -1 : H5PLibrary::query()
            ->join('hh5p_contents_libraries', 'hh5p_libraries.id', '=', 'hh5p_contents_libraries.library_id')
            ->join('hh5p_contents', 'hh5p_contents_libraries.content_id', '=', 'hh5p_contents.id')
            ->where('hh5p_libraries.id', '=', $libraryId)
            ->distinct()
            ->count('hh5p_contents.id');

        $librariesCount = H5PLibrary::query()
            ->with('dependencies')
            ->whereHas('dependencies', fn($query) => $query->whereRequiredLibraryId($libraryId))
            ->count();

        return [
            'content' => $contentsCount,
            'libraries' => $librariesCount,
        ];
    }

    /**
     * Loads a library.
     *
     * @param string $machineName
     *                             The library's machine name
     * @param int    $majorVersion
     *                             The library's major version
     * @param int    $minorVersion
     *                             The library's minor version
     *
     * @return array|false
     *                     FALSE if the library does not exist.
     *                     Otherwise an associative array containing:
     *                     - libraryId: The id of the library if it is an existing library.
     *                     - title: The library's name
     *                     - machineName: The library machineName
     *                     - majorVersion: The library's majorVersion
     *                     - minorVersion: The library's minorVersion
     *                     - patchVersion: The library's patchVersion
     *                     - runnable: 1 if the library is a content type, 0 otherwise
     *                     - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *                     - embedTypes(optional): list of supported embed types
     *                     - preloadedJs(optional): comma separated string with js file paths
     *                     - preloadedCss(optional): comma separated sting with css file paths
     *                     - dropLibraryCss(optional): list of associative arrays containing:
     *                     - machineName: machine name for the librarys that are to drop their css
     *                     - semantics(optional): Json describing the content structure for the library
     *                     - preloadedDependencies(optional): list of associative arrays containing:
     *                     - machineName: Machine name for a library this library is depending on
     *                     - majorVersion: Major version for a library this library is depending on
     *                     - minorVersion: Minor for a library this library is depending on
     *                     - dynamicDependencies(optional): list of associative arrays containing:
     *                     - machineName: Machine name for a library this library is depending on
     *                     - majorVersion: Major version for a library this library is depending on
     *                     - minorVersion: Minor for a library this library is depending on
     *                     - editorDependencies(optional): list of associative arrays containing:
     *                     - machineName: Machine name for a library this library is depending on
     *                     - majorVersion: Major version for a library this library is depending on
     *                     - minorVersion: Minor for a library this library is depending on
     */
    public function loadLibrary($machineName, $majorVersion, $minorVersion)
    {
        $library = H5PLibrary::where([
            'name' => $machineName,
            'major_version' => $majorVersion,
            'minor_version' => $minorVersion,
        ])
            ->with('dependencies.requiredLibrary')
            ->first();

        if (is_null($library)) {
            return;
        }

        $result = $library->toArray();

        foreach ($library->dependencies as $dependency) {
            $result[$dependency->dependencyType . 'Dependencies'][] = [
                'id' => $dependency->requiredLibrary->id,
                'libraryId' => $dependency->requiredLibrary->id,
                'machineName' => $dependency->requiredLibrary->machineName,
                'majorVersion' => $dependency->requiredLibrary->majorVersion,
                'minorVersion' => $dependency->requiredLibrary->minorVersion,
            ];
        }

        return $result;
    }

    /**
     * Loads library semantics.
     *
     * @param string $machineName
     *                             Machine name for the library
     * @param int    $majorVersion
     *                             The library's major version
     * @param int    $minorVersion
     *                             The library's minor version
     *
     * @return string
     *                The library's semantics as json
     */
    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion)
    {
        $library = H5PLibrary::where('name', $machineName)
            ->where('major_version', $majorVersion)
            ->where('minor_version', $minorVersion)
            ->first();

        return $library === false ? null : json_encode($library->semantics);
    }

    /**
     * Makes it possible to alter the semantics, adding custom fields, etc.
     *
     * @param array  $semantics
     *                             Associative array representing the semantics
     * @param string $machineName
     *                             The library's machine name
     * @param int    $majorVersion
     *                             The library's major version
     * @param int    $minorVersion
     *                             The library's minor version
     */
    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion)
    {
    }

    /**
     * Delete all dependencies belonging to given library.
     *
     * @param int $libraryId
     *                       Library identifier
     */
    public function deleteLibraryDependencies($libraryId)
    {
        H5PLibrary::findOrFail($libraryId)->dependencies()->delete();
    }

    /**
     * Start an atomic operation against the dependency storage.
     */
    public function lockDependencyStorage()
    {
    }

    /**
     * Stops an atomic operation against the dependency storage.
     */
    public function unlockDependencyStorage()
    {
    }

    /**
     * Delete a library from database and file system.
     *
     * @param stdClass $library
     *                          Library object with id, name, major version and minor version
     */
    public function deleteLibrary($library)
    {
        $libraryObj = H5pLibrary::with(['dependencies', 'languages'])->findOrFail($library->id);

        // Remove main library from files
        $libraryPath = storage_path('app/h5p/libraries/' . $library->name . '-' . $library->major_version . '.' . $library->minor_version);

        $libraryObj->dependencies()->delete();
        $libraryObj->languages()->delete();
        $libraryObj->delete();

        Helpers::deleteFileTree($libraryPath);

        return true;
    }

    /**
     * Load content.
     *
     * @param int $id
     *                Content identifier
     *
     * @return array
     *               Associative array containing:
     *               - contentId: Identifier for the content
     *               - params: json content as string
     *               - embedType: csv of embed types
     *               - title: The contents title
     *               - language: Language code for the content
     *               - libraryId: Id for the main library
     *               - libraryName: The library machine name
     *               - libraryMajorVersion: The library's majorVersion
     *               - libraryMinorVersion: The library's minorVersion
     *               - libraryEmbedTypes: CSV of the main library's embed types
     *               - libraryFullscreen: 1 if fullscreen is supported. 0 otherwise.
     */
    public function loadContent($id)
    {
        $content = H5PContent::with('library')->where(['id' => $id])->firstOrFail();
        if (is_null($content->library)) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }
        $content = $content->toArray();
        $content['contentId'] = $content['id']; // : Identifier for the content
        $content['params'] = json_encode($content['params']); // : json content as string
        $content['embedType'] = \H5PCore::determineEmbedType($content['embed_type'] ?? 'div', $content['library']['embed_types']); // : csv of embed types
        //$content ['title'] // : The contents title
        //$content ['language'] // : Language code for the content
        $content['libraryId'] = $content['library_id']; // : Id for the main library
        $content['libraryName'] = $content['library']['machineName']; // The library machine name
        $content['libraryMajorVersion'] = $content['library']['majorVersion']; // : The library's majorVersion
        $content['libraryMinorVersion'] = $content['library']['minorVersion']; // : The library's minorVersion
        $content['libraryEmbedTypes'] = $content['library']['embed_types']; // : CSV of the main library's embed types
        $content['libraryFullscreen'] = 0; // : 1 if fullscreen is supported. 0 otherwise.
        //$content ['metadata'] = $content ['metadata'] ?? "";
        $content['metadata'] = json_encode($content['metadata']); // : json content as string
        $content['slug'] = $content['slug'] ?? 'slug';

        return $content;
    }

    /**
     * Load dependencies for the given content of the given type.
     *
     * @param int $id
     *                  Content identifier
     * @param int $type
     *                  Dependency types. Allowed values:
     *                  - editor
     *                  - preloaded
     *                  - dynamic
     *
     * @return array
     *               List of associative arrays containing:
     *               - libraryId: The id of the library if it is an existing library.
     *               - machineName: The library machineName
     *               - majorVersion: The library's majorVersion
     *               - minorVersion: The library's minorVersion
     *               - patchVersion: The library's patchVersion
     *               - preloadedJs(optional): comma separated string with js file paths
     *               - preloadedCss(optional): comma separated sting with css file paths
     *               - dropCss(optional): csv of machine names
     */
    public function loadContentDependencies($id, $type = null)
    {
        $where = $type ? [['content_id', $id], ['dependency_type', $type]] : [['content_id', $id]];

        $libs = H5PContentLibrary::with('library')->where($where)->orderBy('weight')->get();

        return array_map(function ($value) {
            return [
                'libraryId' => $value['library_id'], //: The id of the library if it is an existing library.
                'machineName' => $value['library']['machineName'], //: The library machineName
                'majorVersion' => $value['library']['majorVersion'], //: The library's majorVersion
                'minorVersion' => $value['library']['minorVersion'], //: The library's minorVersion
                'patchVersion' => $value['library']['patchVersion'], //: The library's patchVersion
                'preloadedJs' => $value['library']['preloadedJs'], //(optional): comma separated string with js file paths
                'preloadedCss' => $value['library']['preloadedCss'], //(optional): comma separated sting with css file paths
                'dropCss' => $value['dropCss'], //(optional): csv of machine names
            ];
        }, $libs->toArray());
    }

    /**
     * Get stored setting.
     *
     * @param string $name
     *                        Identifier for the setting
     * @param string $default
     *                        Optional default value if settings is not set
     *
     * @return mixed
     *               Whatever has been stored as the setting
     */
    public function getOption($name, $default = false)
    {
        if ($name === 'site_uuid') {
            $name = 'h5p_site_uuid'; // Make up for old core bug
        }

        switch ($name) {
            case "content_type_cache_updated_at":
                return Cache::get("content_type_cache_updated_at", $default);
                break;
            default:
                return config('hh5p.h5p_' . $name, $default);
        }
    }



    /**
     * Stores the given setting.
     * For example when did we last check h5p.org for updates to our libraries.
     *
     * @param string $name
     *                      Identifier for the setting
     * @param mixed  $value Data
     *                      Whatever we want to store as the setting
     */
    public function setOption($name, $value)
    {
        if ($name === 'site_uuid') {
            $name = 'h5p_site_uuid'; // Make up for old core bug
        }
        switch ($name) {
            case "content_type_cache_updated_at":
                Cache::set("content_type_cache_updated_at", $value);
                break;
            default:
                config(['hh5p.h5p_' . $name => $value]);
        }
    }

    /**
     * This will update selected fields on the given content.
     *
     * @param int   $id     Content identifier
     * @param array $fields Content fields, e.g. filtered or slug.
     */
    public function updateContentFields($id, $fields)
    {
        H5PContent::findOrFail($id)->update($fields);
    }

    /**
     * Will clear filtered params for all the content that uses the specified
     * libraries. This means that the content dependencies will have to be rebuilt,
     * and the parameters re-filtered.
     *
     * @param array $library_ids
     */
    public function clearFilteredParameters($library_ids)
    {
         H5PContent::query()->whereIn('library_id', $library_ids)->update(['filtered' => null]);
    }

    /**
     * Get number of contents that has to get their content dependencies rebuilt
     * and parameters re-filtered.
     *
     * @return int
     */
    public function getNumNotFiltered()
    {
    }

    /**
     * Get number of contents using library as main library.
     *
     * @param int   $libraryId
     * @param array $skip
     *
     * @return int
     */
    public function getNumContent($libraryId, $skip = null)
    {
    }

    /**
     * Determines if content slug is used.
     *
     * @param string $slug
     *
     * @return bool
     */
    public function isContentSlugAvailable($slug)
    {
        return (bool) H5PContent::where(['slug' => $slug])->first();
    }

    /**
     * Generates statistics from the event log per library.
     *
     * @param string $type Type of event to generate stats for
     *
     * @return array Number values indexed by library name and version
     */
    public function getLibraryStats($type)
    {
        return []; // TODO is used in h5p.classes.php => public function fetchLibrariesMetadata($fetchingDisabled = FALSE)
    }

    /**
     * Aggregate the current number of H5P authors.
     *
     * @return int
     */
    public function getNumAuthors()
    {
        return H5PContent::query()->select(['user_id'])->distinct()->count('user_id');
    }

    /**
     * Stores hash keys for cached assets, aggregated JavaScripts and
     * stylesheets, and connects it to libraries so that we know which cache file
     * to delete when a library is updated.
     *
     * @param string $key
     *                          Hash key for the given libraries
     * @param array  $libraries
     *                          List of dependencies(libraries) used to create the key
     */
    public function saveCachedAssets($key, $libraries)
    {
    }

    /**
     * Locate hash keys for given library and delete them.
     * Used when cache file are deleted.
     *
     * @param int $library_id
     *                        Library identifier
     *
     * @return array
     *               List of hash keys removed
     */
    public function deleteCachedAssets($library_id): array
    {
        return [];
    }

    /**
     * Get the amount of content items associated to a library
     * return int.
     */
    public function getLibraryContentCount()
    {
        return H5PLibrary::query()
            ->select('name', 'major_version', 'minor_version')
            ->with('contents')
            ->has('contents')
            ->withCount('contents as count')
            ->get()
            ->mapWithKeys(fn($item, $key) => [$item->uberName => $item->count])
            ->toArray();
    }

    /**
     * Will trigger after the export file is created.
     */
    public function afterExportCreated($content, $filename)
    {
        $this->downloadFiles[intval($content['id'])] = $filename;
    }

    public function getDownloadFile($id)
    {
        return $this->downloadFiles[intval($id)];
    }

    /**
     * Check if user has permissions to an action.
     *
     * @method hasPermission
     *
     * @param [H5PPermission] $permission Permission type, ref H5PPermission
     * @param [int]           $id         Id need by platform to determine permission
     *
     * @return bool
     */
    public function hasPermission($permission, $id = null)
    {
        switch ($permission) {
            case H5PPermission::DOWNLOAD_H5P:
                // var_dump('DOWNLOAD_H5P');
                return true;
            case H5PPermission::EMBED_H5P:
                // var_dump('EMBED_H5P');
                return true;
            case H5PPermission::CREATE_RESTRICTED:
                // var_dump('CREATE_RESTRICTED');
                return true;
            case H5PPermission::UPDATE_LIBRARIES:
                // var_dump('UPDATE_LIBRARIES');
                return true;
            case H5PPermission::INSTALL_RECOMMENDED:
                // var_dump('INSTALL_RECOMMENDED');
                return true;
            case H5PPermission::COPY_H5P:
                // var_dump('COPY_H5P');
                return false;
        }

        // TODO some permissions must be checked here
        // NOTE, in this implementation, we assume that the user has permission to do everything
        // because permissions are set on the request level.

        return true;
    }

    /**
     * Replaces existing content type cache with the one passed in.
     *
     * @param object $contentTypeCache json with an array called 'libraries'
     *                                 containing the new content type cache that should replace the old one
     */
    public function replaceContentTypeCache($contentTypeCache)
    {
        foreach ($contentTypeCache->contentTypes as $ct) {
           $data[] = [
                'machine_name' => $ct->id,
                'major_version' => $ct->version->major,
                'minor_version' => $ct->version->minor,
                'patch_version' => $ct->version->patch,
                'h5p_major_version' => $ct->coreApiVersionNeeded->major,
                'h5p_minor_version' => $ct->coreApiVersionNeeded->minor,
                'title' => $ct->title,
                'summary' => $ct->summary,
                'description' => $ct->description,
                'icon' => $ct->icon,
                'created_at' => (new DateTime($ct->createdAt))->getTimestamp(),
                'updated_at' => (new DateTime($ct->updatedAt))->getTimestamp(),
                'is_recommended' => $ct->isRecommended === true ? 1 : 0,
                'popularity' => $ct->popularity,
                'screenshots' => json_encode($ct->screenshots),
                'license' => json_encode($ct->license ?? []),
                'example' => $ct->example,
                'tutorial' => $ct->tutorial ?? '',
                'keywords' => json_encode($ct->keywords ?? []),
                'categories' => json_encode($ct->categories ?? []),
                'owner' => $ct->owner,
            ];
        }

        DB::transaction(function () use($data) {
            H5pLibrariesHubCache::query()->delete();
            H5pLibrariesHubCache::insert($data);
        });
    }

    /**
     * Checks if the given library has a higher version.
     *
     * @param array $library
     *
     * @return bool
     */
    public function libraryHasUpgrade($library)
    {
    }

    /**
     * Replace content hub metadata cache.
     *
     * @param JsonSerializable $metadata Metadata as received from content hub
     * @param string           $lang     Language in ISO 639-1
     *
     * @return mixed
     */
    public function replaceContentHubMetadataCache($metadata, $lang)
    {
    }

    /**
     * Get content hub metadata cache from db.
     *
     * @param string $lang Language code in ISO 639-1
     *
     * @return JsonSerializable Json string
     */
    public function getContentHubMetadataCache($lang = 'en')
    {
    }

    /**
     * Get time of last content hub metadata check.
     *
     * @param string $lang Language code iin ISO 639-1 format
     *
     * @return string|null Time in RFC7231 format
     */
    public function getContentHubMetadataChecked($lang = 'en')
    {
    }

    /**
     * Set time of last content hub metadata check.
     *
     * @param int|null $time Time in RFC7231 format
     * @param string   $lang Language code iin ISO 639-1 format
     *
     * @return bool True if successful
     */
    public function setContentHubMetadataChecked($time, $lang = 'en')
    {
    }
}
