<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use H5PFrameworkInterface;
use Illuminate\Support\Facades\Log;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;

use EscolaLms\HeadlessH5P\Helpers\Helpers;

class H5PRepository implements H5PFrameworkInterface
{
    protected $messages = ['error' => [], 'updated' => []];

    /**
   * Returns info for the current platform
   *
   * @return array
   *   An associative array containing:
   *   - name: The name of the platform, for instance "Wordpress"
   *   - version: The version of the platform, for instance "4.0"
   *   - h5pVersion: The version of the H5P plugin/module
   */
    public function getPlatformInfo()
    {
    }


    /**
     * Fetches a file from a remote server using HTTP GET
     *
     * @param  string  $url  Where you want to get or send data.
     * @param  array  $data  Data to post to the URL.
     * @param  bool  $blocking  Set to 'FALSE' to instantly time out (fire and forget).
     * @param  string  $stream  Path to where the file should be saved.
     * @param  bool  $fullData  Return additional response data such as headers and potentially other data
     * @param  array  $headers  Headers to send
     * @param  array  $files Files to send
     * @param  string  $method
     *
     * @return string|array The content (response body), or an array with data. NULL if something went wrong
     */
    public function fetchExternalData($url, $data = null, $blocking = true, $stream = null, $fullData = false, $headers = array(), $files = array(), $method = 'POST')
    {
    }

    /**
     * Set the tutorial URL for a library. All versions of the library is set
     *
     * @param string $machineName
     * @param string $tutorialUrl
     */
    public function setLibraryTutorialUrl($machineName, $tutorialUrl)
    {
    }

    /**
     * Show the user an error message
     *
     * @param string $message The error message
     * @param string $code An optional code
     */
    public function setErrorMessage($message, $code = null)
    {
        $this->messages['error'][] = $message;
    }

    /**
     * Show the user an information message
     *
     * @param string $message
     *  The error message
     */
    public function setInfoMessage($message)
    {
        $this->messages['updated'][] = $message;
    }

    /**
     * Return messages
     *
     * @param string $type 'info' or 'error'
     * @return string[]
     */
    public function getMessages($type = 'error')
    {
        return isset($this->messages[$type]) ? $this->messages[$type] : null;
    }

    /**
     * Translation function
     *
     * @param string $message
     *  The english string to be translated.
     * @param array $replacements
     *   An associative array of replacements to make after translation. Incidences
     *   of any key in this array are replaced with the corresponding value. Based
     *   on the first character of the key, the value is escaped and/or themed:
     *    - !variable: inserted as is
     *    - @variable: escape plain text to HTML
     *    - %variable: escape text and theme as a placeholder for user-submitted
     *      content
     * @return string Translated string
     * Translated string
     */
    public function t($message, $replacements = array())
    {
        // Insert !var as is, escape @var and emphasis %var.
        foreach ($replacements as $key => $replacement) {
            if ($key[0] === '@') {
                //                $replacements[$key] = esc_html($replacement);
                $replacements[$key] = $replacement;
            } elseif ($key[0] === '%') {
                //                $replacements[$key] = '<em>' . esc_html($replacement) . '</em>';
                $replacements[$key] = '<em>'.$replacement.'</em>';
            }
        }
        $message = preg_replace('/(!|@|%)[a-z0-9]+/i', '%s', $message);

        // Assumes that replacement vars are in the correct order.
        return vsprintf(trans($message), $replacements);
    }

    /**
     * Get URL to file in the specific library
     * @param string $libraryFolderName
     * @param string $fileName
     * @return string URL to file
     */
    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {
    }

    /**
     * Get the Path to the last uploaded h5p
     *
     * @return string
     *   Path to the folder where the last uploaded h5p for this session is located.
     */
    public function getUploadedH5pFolderPath()
    {
        static $dir; // such a stupid way to have singleton ....
        if (is_null($dir)) {
            $dir = storage_path('app/h5p/temp/temp/').uniqid('h5p-');
            @mkdir(dirname($dir), 0777, true);
        }
        return  $dir;
    }

    /**
     * Get the path to the last uploaded h5p file
     *
     * @return string
     *   Path to the last uploaded h5p
     */
    public function getUploadedH5pPath()
    {
        static $path; // such a stupid way to have singleton ....
        if (is_null($path)) {
            $path = storage_path('app/h5p/temp/temp/').uniqid('h5p-').'.h5p';
            @mkdir(dirname($path), 0777, true);
        }
        return  $path;
    }

    /**
     * Load addon libraries
     *
     * @return array
     */
    public function loadAddons()
    {
    }

    /**
     * Load config for libraries
     *
     * @param array $libraries
     * @return array
     */
    public function getLibraryConfig($libraries = null)
    {
    }

    /**
     * Get a list of the current installed libraries
     *
     * @return array
     *   Associative array containing one entry per machine name.
     *   For each machineName there is a list of libraries(with different versions)
     */
    public function loadLibraries()
    {
    }

    /**
     * Returns the URL to the library admin page
     *
     * @return string
     *   URL to admin page
     */
    public function getAdminUrl()
    {
    }

    /**
     * Get id to an existing library.
     * If version number is not specified, the newest version will be returned.
     *
     * @param string $machineName
     *   The librarys machine name
     * @param int $majorVersion
     *   Optional major version number for library
     * @param int $minorVersion
     *   Optional minor version number for library
     * @return int
     *   The id of the specified library or FALSE
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
     * Get file extension whitelist
     *
     * The default extension list is part of h5p, but admins should be allowed to modify it
     *
     * @param boolean $isLibrary
     *   TRUE if this is the whitelist for a library. FALSE if it is the whitelist
     *   for the content folder we are getting
     * @param string $defaultContentWhitelist
     *   A string of file extensions separated by whitespace
     * @param string $defaultLibraryWhitelist
     *   A string of file extensions separated by whitespace
     */
    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
    {
        $whitelist = $defaultContentWhitelist;
        if ($isLibrary) {
            $whitelist .= ' '.$defaultLibraryWhitelist;
        }

        return $whitelist;
    }

    /**
     * Is the library a patched version of an existing library?
     *
     * @param object $library
     *   An associative array containing:
     *   - machineName: The library machineName
     *   - majorVersion: The librarys majorVersion
     *   - minorVersion: The librarys minorVersion
     *   - patchVersion: The librarys patchVersion
     * @return boolean
     *   TRUE if the library is a patched version of an existing library
     *   FALSE otherwise
     */
    public function isPatchedLibrary($library)
    {
    }

    /**
     * Is H5P in development mode?
     *
     * @return boolean
     *  TRUE if H5P development mode is active
     *  FALSE otherwise
     */
    public function isInDevMode()
    {
        return true;
    }

    /**
     * Is the current user allowed to update libraries?
     *
     * @return boolean
     *  TRUE if the user is allowed to update libraries
     *  FALSE if the user is not allowed to update libraries
     */
    public function mayUpdateLibraries()
    {
        // TODO check if current user is allowed to do this action
        // best option use middleware on router
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
     * Store data about a library
     *
     * Also fills in the libraryId in the libraryData object if the object is new
     *
     * @param object $libraryData
     *   Associative array containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - title: The library's name
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - runnable: 1 if the library is a content type, 0 otherwise
     *   - metadataSettings: Associative array containing:
     *      - disable: 1 if the library should not support setting metadata (copyright etc)
     *      - disableExtraTitleField: 1 if the library don't need the extra title field
     *   - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *   - embedTypes(optional): list of supported embed types
     *   - preloadedJs(optional): list of associative arrays containing:
     *     - path: path to a js file relative to the library root folder
     *   - preloadedCss(optional): list of associative arrays containing:
     *     - path: path to css file relative to the library root folder
     *   - dropLibraryCss(optional): list of associative arrays containing:
     *     - machineName: machine name for the librarys that are to drop their css
     *   - semantics(optional): Json describing the content structure for the library
     *   - language(optional): associative array containing:
     *     - languageCode: Translation in json format
     * @param bool $new
     * @return
     */
    public function saveLibraryData(&$libraryData, $new = true)
    {
        $library = [
            'name'             => $libraryData['machineName'],
            'title'            => $libraryData['title'],
            'major_version'    => $libraryData['majorVersion'],
            'minor_version'    => $libraryData['minorVersion'],
            'patch_version'    => $libraryData['patchVersion'],
            'runnable'         => $libraryData['runnable'],
            'fullscreen'       => isset($libraryData['fullscreen']) ? $libraryData['fullscreen'] : 0,
            'embed_types'      => isset($libraryData['embedTypes']) ? implode(', ', $libraryData['embedTypes']) : '',
            'preloaded_js'     => $this->pathsToCsv($libraryData, 'preloadedJs'),
            'preloaded_css'    => $this->pathsToCsv($libraryData, 'preloadedCss'),
            'drop_library_css' => '', // TODO, what is this ?
            'semantics'        => isset($libraryData['semantics']) ? $libraryData['semantics'] : '',
            'tutorial_url'     => isset($libraryData['tutorial_url']) ? isset($libraryData['tutorial_url']) : '',
            'has_icon'         => isset($libraryData['hasIcon']) ? 1 : 0,
        ];

        $libObj = H5PLibrary::firstOrCreate($library);
        
        $library['libraryId'] = $libObj->id;
        $this->deleteLibraryDependencies($library['libraryId']);

        // This is essential, as this method should mutate the `libraryData`
        // I hate mutations

        $libraryData = array_merge($libraryData, $library);
    }

    /**
     * Insert new content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versions
     */
    public function insertContent($content, $contentMainId = null)
    {
    }

    /**
     * Update old content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versions
     */
    public function updateContent($content, $contentMainId = null)
    {
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
     * Save what libraries a library is depending on
     *
     * @param int $libraryId
     *   Library Id for the library we're saving dependencies for
     * @param array $dependencies
     *   List of dependencies as associative arrays containing:
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     * @param string $dependency_type
     *   What type of dependency this is, the following values are allowed:
     *   - editor
     *   - preloaded
     *   - dynamic
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
                'library_id'=>$libraryId,
                'required_library_id'=>$dbLib->id,
                'dependency_type'=>$dependency_type
            ]);
        }

        return true;
    }

    /**
     * Give an H5P the same library dependencies as a given H5P
     *
     * @param int $contentId
     *   Id identifying the content
     * @param int $copyFromId
     *   Id identifying the content to be copied
     * @param int $contentMainId
     *   Main id for the content, typically used in frameworks
     *   That supports versions. (In this case the content id will typically be
     *   the version id, and the contentMainId will be the frameworks content id
     */
    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = null)
    {
    }

    /**
     * Deletes content data
     *
     * @param int $contentId
     *   Id identifying the content
     */
    public function deleteContentData($contentId)
    {
    }

    /**
     * Delete what libraries a content item is using
     *
     * @param int $contentId
     *   Content Id of the content we'll be deleting library usage for
     */
    public function deleteLibraryUsage($contentId)
    {
    }

    /**
     * Saves what libraries the content uses
     *
     * @param int $contentId
     *   Id identifying the content
     * @param array $librariesInUse
     *   List of libraries the content uses. Libraries consist of associative arrays with:
     *   - library: Associative array containing:
     *     - dropLibraryCss(optional): comma separated list of machineNames
     *     - machineName: Machine name for the library
     *     - libraryId: Id of the library
     *   - type: The dependency type. Allowed values:
     *     - editor
     *     - dynamic
     *     - preloaded
     */
    public function saveLibraryUsage($contentId, $librariesInUse)
    {
    }

    /**
     * Get number of content/nodes using a library, and the number of
     * dependencies to other libraries
     *
     * @param int $libraryId
     *   Library identifier
     * @param boolean $skipContent
     *   Flag to indicate if content usage should be skipped
     * @return array
     *   Associative array containing:
     *   - content: Number of content using the library
     *   - libraries: Number of libraries depending on the library
     */
    public function getLibraryUsage($libraryId, $skipContent = false)
    {
    }

    /**
     * Loads a library
     *
     * @param string $machineName
     *   The library's machine name
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return array|FALSE
     *   FALSE if the library does not exist.
     *   Otherwise an associative array containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - title: The library's name
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - runnable: 1 if the library is a content type, 0 otherwise
     *   - fullscreen(optional): 1 if the library supports fullscreen, 0 otherwise
     *   - embedTypes(optional): list of supported embed types
     *   - preloadedJs(optional): comma separated string with js file paths
     *   - preloadedCss(optional): comma separated sting with css file paths
     *   - dropLibraryCss(optional): list of associative arrays containing:
     *     - machineName: machine name for the librarys that are to drop their css
     *   - semantics(optional): Json describing the content structure for the library
     *   - preloadedDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     *   - dynamicDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     *   - editorDependencies(optional): list of associative arrays containing:
     *     - machineName: Machine name for a library this library is depending on
     *     - majorVersion: Major version for a library this library is depending on
     *     - minorVersion: Minor for a library this library is depending on
     */
    public function loadLibrary($machineName, $majorVersion, $minorVersion)
    {
        $library = H5PLibrary::where([
            'name' => $machineName,
            'major_version'=> $majorVersion,
            'minor_version'=>$minorVersion])
        ->with('dependencies.requiredLibrary')
        ->first();



        if (is_null($library)) {
            return;
        }

        //dd($library->toArray());

        $result =  $library->toArray();

        foreach ($library->dependencies as $dependency) {
            $result [$dependency->dependencyType.'Dependencies'][] = [
                'id' => $dependency->requiredLibrary->id,
                'libraryId' => $dependency->requiredLibrary->id,
                'machineName'  => $dependency->requiredLibrary->machineName,
                'majorVersion' => $dependency->requiredLibrary->majorVersion,
                'minorVersion' => $dependency->requiredLibrary->minorVersion,
            ];
        }

//        dd($result);

        //dd($result);

        return $result;


        //dd($result);

        //Helpers::fixCaseKeysArray(['libraryId', 'machineName', 'majorVersion', 'minorVersion', 'patchVersion', 'embedTypes', 'preloadedJs', 'preloadedCss', 'dropLibraryCss', 'hasIcon'], $library);



        /*

        $dependencies = DB::select('SELECT hl.name as machineName, hl.major_version as majorVersion, hl.minor_version as minorVersion, hll.dependency_type as dependencyType
            FROM h5p_libraries_libraries hll
            JOIN h5p_libraries hl ON hll.required_library_id = hl.id
            WHERE hll.library_id = ?', [$library->libraryId]);

            self::fixCaseKeysArray([ 'machineName', 'majorVersion', 'minorVersion', 'dependencyType'], $dependencies);


        foreach ($dependencies as $dependency) {
            $return[$dependency->dependencyType.'Dependencies'][] = [
                'machineName'  => $dependency->machineName,
                'majorVersion' => $dependency->majorVersion,
                'minorVersion' => $dependency->minorVersion,
            ];
        }
        */

     
        /*
        $uberName = $name . ' ' . $majorVersion . '.' . $minorVersion;

        if (in_array($uberName, array_keys($this->loadedLibraries))) {
            return $this->loadedLibraries[$uberName];
        }

        if (config('laravel-h5p.preload_all_libraries')) {
            if (count($this->allLibraries) == 0) {
                $this->allLibraries = DB::table('h5p_libraries')
                    ->select(['id as libraryId', 'name as machineName', 'title', 'major_version as majorVersion', 'minor_version as minorVersion', 'patch_version as patchVersion', 'embed_types as embedTypes', 'preloaded_js as preloadedJs', 'preloaded_css as preloadedCss', 'drop_library_css as dropLibraryCss', 'fullscreen', 'runnable', 'semantics', 'has_icon as hasIcon'])
                    ->get();

                self::fixCaseKeysArray(['libraryId', 'machineName', 'majorVersion', 'minorVersion', 'patchVersion', 'embedTypes', 'preloadedJs', 'preloadedCss', 'dropLibraryCss', 'hasIcon'], $this->allLibraries);
            }

            $library = $this->allLibraries->filter(function ($val) use ($name, $majorVersion, $minorVersion) {
                return $val->machineName == $name
                    && $val->majorVersion == $majorVersion
                    && $val->minorVersion == $minorVersion;
            })->first();
        } else {
            $library = DB::table('h5p_libraries')
                ->select(['id as libraryId', 'name as machineName', 'title', 'major_version as majorVersion', 'minor_version as minorVersion', 'patch_version as patchVersion', 'embed_types as embedTypes', 'preloaded_js as preloadedJs', 'preloaded_css as preloadedCss', 'drop_library_css as dropLibraryCss', 'fullscreen', 'runnable', 'semantics', 'has_icon as hasIcon'])
                ->where('name', $name)
                ->where('major_version', $majorVersion)
                ->where('minor_version', $minorVersion)
                ->first();

            self::fixCaseKeysArray(['libraryId', 'machineName', 'majorVersion', 'minorVersion', 'patchVersion', 'embedTypes', 'preloadedJs', 'preloadedCss', 'dropLibraryCss', 'hasIcon'], $library);
        }

        $return = json_decode(json_encode($library), true);

        if (config('laravel-h5p.preload_all_libraries')) {
            if (count($this->allDependencies) == 0) {
                $this->allDependencies = collect(json_decode(json_encode(DB::select('SELECT hl.name as machineName, hl.major_version as majorVersion, hl.minor_version as minorVersion, hll.dependency_type as dependencyType, hll.library_id as libraryId
                FROM h5p_libraries_libraries hll
                JOIN h5p_libraries hl ON hll.required_library_id = hl.id'))));
                self::fixCaseKeysArray([ 'machineName', 'majorVersion', 'minorVersion', 'dependencyType', 'libraryId'], $this->allDependencies);
            }

            $dependencies = $this->allDependencies->filter(function ($val) use ($library) {
                return $val->libraryId == $library->libraryId;
            });
        } else {
            $dependencies = DB::select('SELECT hl.name as machineName, hl.major_version as majorVersion, hl.minor_version as minorVersion, hll.dependency_type as dependencyType
            FROM h5p_libraries_libraries hll
            JOIN h5p_libraries hl ON hll.required_library_id = hl.id
            WHERE hll.library_id = ?', [$library->libraryId]);

            self::fixCaseKeysArray([ 'machineName', 'majorVersion', 'minorVersion', 'dependencyType'], $dependencies);
        }

        foreach ($dependencies as $dependency) {
            $return[$dependency->dependencyType.'Dependencies'][] = [
                'machineName'  => $dependency->machineName,
                'majorVersion' => $dependency->majorVersion,
                'minorVersion' => $dependency->minorVersion,
            ];
        }
        if ($this->isInDevMode()) {
            $semantics = $this->getSemanticsFromFile($return['machineName'], $return['majorVersion'], $return['minorVersion']);
            if ($semantics) {
                $return['semantics'] = $semantics;
            }
        }

        $this->loadedLibraries[$uberName] = $return;
        return $return;
        */
    }


    /**
     * Loads library semantics.
     *
     * @param string $machineName
     *   Machine name for the library
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return string
     *   The library's semantics as json
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
     * @param array $semantics
     *   Associative array representing the semantics
     * @param string $machineName
     *   The library's machine name
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     */
    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion)
    {
    }

    /**
     * Delete all dependencies belonging to given library
     *
     * @param int $libraryId
     *   Library identifier
     */
    public function deleteLibraryDependencies($libraryId)
    {
    }

    /**
     * Start an atomic operation against the dependency storage
     */
    public function lockDependencyStorage()
    {
    }

    /**
     * Stops an atomic operation against the dependency storage
     */
    public function unlockDependencyStorage()
    {
    }


    /**
     * Delete a library from database and file system
     *
     * @param stdClass $library
     *   Library object with id, name, major version and minor version.
     */
    public function deleteLibrary($library)
    {
    }

    /**
     * Load content.
     *
     * @param int $id
     *   Content identifier
     * @return array
     *   Associative array containing:
     *   - contentId: Identifier for the content
     *   - params: json content as string
     *   - embedType: csv of embed types
     *   - title: The contents title
     *   - language: Language code for the content
     *   - libraryId: Id for the main library
     *   - libraryName: The library machine name
     *   - libraryMajorVersion: The library's majorVersion
     *   - libraryMinorVersion: The library's minorVersion
     *   - libraryEmbedTypes: CSV of the main library's embed types
     *   - libraryFullscreen: 1 if fullscreen is supported. 0 otherwise.
     */
    public function loadContent($id)
    {
    }

    /**
     * Load dependencies for the given content of the given type.
     *
     * @param int $id
     *   Content identifier
     * @param int $type
     *   Dependency types. Allowed values:
     *   - editor
     *   - preloaded
     *   - dynamic
     * @return array
     *   List of associative arrays containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - preloadedJs(optional): comma separated string with js file paths
     *   - preloadedCss(optional): comma separated sting with css file paths
     *   - dropCss(optional): csv of machine names
     */
    public function loadContentDependencies($id, $type = null)
    {
    }

    /**
     * Get stored setting.
     *
     * @param string $name
     *   Identifier for the setting
     * @param string $default
     *   Optional default value if settings is not set
     * @return mixed
     *   Whatever has been stored as the setting
     */
    public function getOption($name, $default = null)
    {
    }

    /**
     * Stores the given setting.
     * For example when did we last check h5p.org for updates to our libraries.
     *
     * @param string $name
     *   Identifier for the setting
     * @param mixed $value Data
     *   Whatever we want to store as the setting
     */
    public function setOption($name, $value)
    {
    }

    /**
     * This will update selected fields on the given content.
     *
     * @param int $id Content identifier
     * @param array $fields Content fields, e.g. filtered or slug.
     */
    public function updateContentFields($id, $fields)
    {
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
     * @param int $libraryId
     * @param array $skip
     * @return int
     */
    public function getNumContent($libraryId, $skip = null)
    {
    }

    /**
     * Determines if content slug is used.
     *
     * @param string $slug
     * @return boolean
     */
    public function isContentSlugAvailable($slug)
    {
    }

    /**
     * Generates statistics from the event log per library
     *
     * @param string $type Type of event to generate stats for
     * @return array Number values indexed by library name and version
     */
    public function getLibraryStats($type)
    {
    }

    /**
     * Aggregate the current number of H5P authors
     * @return int
     */
    public function getNumAuthors()
    {
    }

    /**
     * Stores hash keys for cached assets, aggregated JavaScripts and
     * stylesheets, and connects it to libraries so that we know which cache file
     * to delete when a library is updated.
     *
     * @param string $key
     *  Hash key for the given libraries
     * @param array $libraries
     *  List of dependencies(libraries) used to create the key
     */
    public function saveCachedAssets($key, $libraries)
    {
    }

    /**
     * Locate hash keys for given library and delete them.
     * Used when cache file are deleted.
     *
     * @param int $library_id
     *  Library identifier
     * @return array
     *  List of hash keys removed
     */
    public function deleteCachedAssets($library_id)
    {
    }

    /**
     * Get the amount of content items associated to a library
     * return int
     */
    public function getLibraryContentCount()
    {
    }

    /**
     * Will trigger after the export file is created.
     */
    public function afterExportCreated($content, $filename)
    {
    }

    /**
     * Check if user has permissions to an action
     *
     * @method hasPermission
     * @param  [H5PPermission] $permission Permission type, ref H5PPermission
     * @param  [int]           $id         Id need by platform to determine permission
     * @return boolean
     */
    public function hasPermission($permission, $id = null)
    {
    }

    /**
     * Replaces existing content type cache with the one passed in
     *
     * @param object $contentTypeCache Json with an array called 'libraries'
     *  containing the new content type cache that should replace the old one.
     */
    public function replaceContentTypeCache($contentTypeCache)
    {
    }

    /**
     * Checks if the given library has a higher version.
     *
     * @param array $library
     * @return boolean
     */
    public function libraryHasUpgrade($library)
    {
    }

    /**
     * Replace content hub metadata cache
     *
     * @param JsonSerializable $metadata Metadata as received from content hub
     * @param string $lang Language in ISO 639-1
     *
     * @return mixed
     */
    public function replaceContentHubMetadataCache($metadata, $lang)
    {
    }

    /**
     * Get content hub metadata cache from db
     *
     * @param  string  $lang Language code in ISO 639-1
     *
     * @return JsonSerializable Json string
     */
    public function getContentHubMetadataCache($lang = 'en')
    {
    }

    /**
     * Get time of last content hub metadata check
     *
     * @param  string  $lang Language code iin ISO 639-1 format
     *
     * @return string|null Time in RFC7231 format
     */
    public function getContentHubMetadataChecked($lang = 'en')
    {
    }

    /**
     * Set time of last content hub metadata check
     *
     * @param  int|null  $time Time in RFC7231 format
     * @param  string  $lang Language code iin ISO 639-1 format
     *
     * @return bool True if successful
     */
    public function setContentHubMetadataChecked($time, $lang = 'en')
    {
    }
}
