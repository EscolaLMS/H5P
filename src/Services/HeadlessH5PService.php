<?php

namespace EscolaLms\HeadlessH5P\Services;

use EscolaLms\HeadlessH5P\Exceptions\H5PException;
use EscolaLms\HeadlessH5P\Helpers\MargeFiles;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use H5PContentValidator;
use H5PCore;
use H5peditor;
use H5PEditorAjaxInterface;
use H5peditorFile;
use H5peditorStorage;
use H5PFileStorage;
use H5PFrameworkInterface;
use H5PStorage;
use H5PValidator;
use H5PPermission;
use H5PHubEndpoints;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

class HeadlessH5PService implements HeadlessH5PServiceContract
{
    private H5PFrameworkInterface $repository;
    private H5PFileStorage $fileStorage;
    private H5PCore $core;
    private H5PValidator $validator;
    private H5PStorage $storage;
    private H5peditorStorage $editorStorage;
    private H5PEditorAjaxInterface $editorAjaxRepository;
    private H5PContentValidator $contentValidator;
    private array $config;

    public function __construct(
        H5PFrameworkInterface  $repository,
        H5PFileStorage         $fileStorage,
        H5PCore                $core,
        H5PValidator           $validator,
        H5PStorage             $storage,
        H5peditorStorage       $editorStorage,
        H5PEditorAjaxInterface $editorAjaxRepository,
        H5peditor              $editor,
        H5PContentValidator    $contentValidator
    )
    {
        $this->repository = $repository;
        $this->fileStorage = $fileStorage;
        $this->core = $core;
        $this->validator = $validator;
        $this->storage = $storage;
        $this->editorStorage = $editorStorage;
        $this->editorAjaxRepository = $editorAjaxRepository;
        $this->editor = $editor;
        $this->contentValidator = $contentValidator;
    }

    public function getEditor(): H5peditor
    {
        return $this->editor;
    }

    public function getRepository(): H5PFrameworkInterface
    {
        return $this->repository;
    }

    public function getFileStorage(): H5PFileStorage
    {
        return $this->fileStorage;
    }

    public function getCore(): H5PCore
    {
        return $this->core;
    }

    public function getValidator(): H5PValidator
    {
        return $this->validator;
    }

    public function getStorage(): H5PStorage
    {
        return $this->storage;
    }

    public function getEditorStorage(): H5peditorStorage
    {
        return $this->editorStorage;
    }

    public function getContentValidator(): H5PContentValidator
    {
        return $this->contentValidator;
    }

    /** Copy file to `getUploadedH5pPath` and validates its contents */
    public function validatePackage(UploadedFile $file, $skipContent = true, $h5p_upgrade_only = false): bool
    {
        rename($file->getPathName(), $this->getRepository()->getUploadedH5pPath());
        try {
            $isValid = $this->getValidator()->isValidPackage($skipContent, $h5p_upgrade_only);
        } catch (Exception $err) {
            var_dump($err);
        }

        return $isValid;
    }

    /**
     * Saves a H5P file.
     *
     * @param null $content
     * @param int $contentMainId
     *                            The main id for the content we are saving. This is used if the framework
     *                            we're integrating with uses content id's and version id's
     *
     * @return bool TRUE if one or more libraries were updated
     *              TRUE if one or more libraries were updated
     *              FALSE otherwise
     */
    public function savePackage(object $content = null, int $contentMainId = null, bool $skipContent = true, array $options = []): bool
    { // this is crazy, it does save package from `getUploadedH5pPath` path
        try {
            $this->getStorage()->savePackage($content, $contentMainId, $skipContent, $options);
        } catch (Exception $err) {
            return false;
        }

        return true;
    }

    public function getMessages($type = 'error')
    {
        return $this->getRepository()->getMessages($type);
    }

    public function listLibraries(): Collection
    {
        return H5PLibrary::all();
    }

    public function getConfig(): array
    {
        if (!isset($this->config)) {
            $config = (array)config('hh5p');
            $config['url'] = asset($config['url']);
            $config['ajaxPath'] = route($config['ajaxPath']) . '/';
            $config['libraryUrl'] = url($config['libraryUrl']) . '/';
            $config['get_laravelh5p_url'] = url($config['get_laravelh5p_url']);
            $config['get_h5peditor_url'] = url($config['get_h5peditor_url']) . '/';
            $config['get_h5pcore_url'] = url($config['get_h5pcore_url']);
            $config['getCopyrightSemantics'] = $this->getContentValidator()->getCopyrightSemantics();
            $config['getMetadataSemantics'] = $this->getContentValidator()->getMetadataSemantics();
            $config['filesPath'] = url('h5p/editor'); // TODO: diffrernt name
            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * Calls editor ajax actions.
     */
    public function getLibraries(string $machineName = null, string $major_version = null, string $minor_version = null)
    {
        $lang = config('hh5p.language');

        $libraries_url = url(config('hh5p.h5p_library_url'));
        if ($machineName) {
            $defaultLang = $this->getEditor()->getLibraryLanguage($machineName, $major_version, $minor_version, $lang);

            return $this->getEditor()->getLibraryData($machineName, $major_version, $minor_version, $lang, '', $libraries_url, $defaultLang);
        }

        return $this->getEditor()->getLibraries();
    }

    public function getEditorSettings($content = null): array
    {
        $lang = config('hh5p.language');

        $config = $this->getConfig();

        $settings = [
            'baseUrl' => $config['domain'],
            'url' => $config['url'],
            'postUserStatistics' => config('hh5p.h5p_track_user'),
            'ajax' => [
                'setFinished' => $config['ajaxSetFinished'],
                'contentUserData' => $config['ajaxContentUserData'],
            ],
            'saveFreq' => false,
            'siteUrl' => $config['domain'],
            'l10n' => [
                'H5P' => __('h5p::h5p')['h5p'],
            ],
            'hubIsEnabled' => config('hh5p.h5p_hub_is_enabled'),
            'crossorigin' => 'anonymous',
        ];

        $settings['loadedJs'] = [];
        $settings['loadedCss'] = [];

        $settings['core'] = [
            'styles' => [],
            'scripts' => [],
        ];
        foreach (H5PCore::$styles as $style) {
            $settings['core']['styles'][] = $config['get_h5pcore_url'] . '/' . $style;
        }
        foreach (H5PCore::$scripts as $script) {
            $settings['core']['scripts'][] = $config['get_h5pcore_url'] . '/' . $script;
        }
        $settings['core']['scripts'][] = $config['get_h5peditor_url'] . '/scripts/h5peditor-editor.js';
        $settings['core']['scripts'][] = $config['get_h5peditor_url'] . '/scripts/h5peditor-init.js';
        $settings['core']['scripts'][] = $config['get_h5peditor_url'] . '/language/'. $lang .'.js';

        $settings['editor'] = [
            'filesPath' => isset($content) ? url("h5p/content/$content") : url('h5p/editor'),
            'fileIcon' => [
                'path' => $config['fileIcon'],
                'width' => 50,
                'height' => 50,
            ],
            //'ajaxPath' => route('h5p.ajax').'/?_token=' . $token ,
            'ajaxPath' => $config['ajaxPath'],
            // for checkeditor,
            'libraryUrl' => $config['libraryUrl'],
            'copyrightSemantics' => $config['getCopyrightSemantics'],
            'metadataSemantics' => $config['getMetadataSemantics'],
            'assets' => [],
            'deleteMessage' => trans('laravel-h5p.content.destoryed'),
            'apiVersion' => H5PCore::$coreApi,
        ];

        if ($content !== null) {
            $settings['contents']["cid-$content"] = $this->getSettingsForContent($content);
            $settings['editor']['nodeVersionId'] = $content;
            $settings['nonce'] = $settings['contents']["cid-$content"]['nonce'];
        } else {
            $settings['nonce'] = bin2hex(random_bytes(4));
        }

        $settings['filesAjaxPath'] = URL::temporarySignedRoute(
            'hh5p.files.upload.nonce',
            now()->addMinutes(30),
            ['nonce' => $settings['nonce']]
        );

        // load core assets
        $settings['editor']['assets']['css'] = $settings['core']['styles'];
        $settings['editor']['assets']['js'] = $settings['core']['scripts'];

        // add editor styles
        foreach (H5peditor::$styles as $style) {
            $settings['editor']['assets']['css'][] = $config['get_h5peditor_url'] . ('/' . $style);
        }
        // Add editor JavaScript
        foreach (H5peditor::$scripts as $script) {
            // We do not want the creator of the iframe inside the iframe
            if ($script !== 'scripts/h5peditor-editor.js') {
                $settings['editor']['assets']['js'][] = $config['get_h5peditor_url'] . ('/' . $script);
            }
        }

        $language_script = '/language/' . $lang . '.js';

        $settings['editor']['assets']['js'][] = $config['get_h5peditor_url'] . ($language_script);

        $h5pEditorDir = file_exists(__DIR__ . '/../../vendor/h5p/h5p-editor')
            ? __DIR__ . '/../../vendor/h5p/h5p-editor'
            : __DIR__ . '/../../../../../vendor/h5p/h5p-editor';
        $h5pCoreDir = file_exists(__DIR__ . '/../../vendor/h5p/h5p-core')
            ? __DIR__ . '/../../vendor/h5p/h5p-core'
            : __DIR__ . '/../../../../../vendor/h5p/h5p-core';


        $settings['core']['scripts'] = $this->margeFileList(
            $settings['core']['scripts'],
            'js',
            [$config['get_h5peditor_url'], $config['get_h5pcore_url']],
            [$h5pEditorDir, $h5pCoreDir]
        );

        // TODO. Those css should be merged as well but I don't know how to do that
        // because there are @import url(...) in the css files that must be amended to the correct path
        // eg changing from relative to absolute path

        /*$settings['core']['styles'] = $this->margeFileList(
            $settings['core']['styles'],
            'css',
            [$config['get_h5peditor_url'], $config['get_h5pcore_url']],
            [$h5pEditorDir, $h5pCoreDir]
        );*/


        $settings['editor']['assets']['js'] = $this->margeFileList(
            $settings['editor']['assets']['js'],
            'js',
            [$config['get_h5peditor_url'], $config['get_h5pcore_url']],
            [$h5pEditorDir, $h5pCoreDir]
        );

        /*$settings['editor']['assets']['css'] = $this->margeFileList(
            $settings['editor']['assets']['css'],
            'css',
            [$config['get_h5peditor_url'], $config['get_h5pcore_url']],
            [$h5pEditorDir, $h5pCoreDir]
        );*/

        if ($content) {
            $preloaded_dependencies = $this->getCore()->loadContentDependencies($content, 'preloaded');
            $files = $this->getCore()->getDependenciesFiles($preloaded_dependencies);
            $cid = $settings['contents']["cid-$content"];
            $embed = H5PCore::determineEmbedType($cid['content']['embed_type'] ?? 'div', $cid['content']['library']['embedTypes']);

            $scripts = array_map(function ($value) use ($config) {
                return $config['url'] . ($value->path . $value->version);
            }, $files['scripts']);

            $styles = array_map(function ($value) use ($config) {
                return $config['url'] . ($value->path . $value->version);
            }, $files['styles']);

            // Error with scripts order

            $settings['contents']["cid-$content"]['scripts'] = $scripts;
            $settings['contents']["cid-$content"]['styles'] = $styles;

            /*
            if ($embed === 'div') {
            foreach ($files['scripts'] as $script) {
                $url = $script->path.$script->version;
                if (!in_array($url, $settings['loadedJs'])) {
                    $settings['loadedJs'][] = $config['url'].($url);
                }
            }
            foreach ($files['styles'] as $style) {
                $url = $style->path.$style->version;
                if (!in_array($url, $settings['loadedCss'])) {
                    $settings['loadedCss'][] = $config['url'].($url);
                }
            }
            } elseif ($embed === 'iframe') {
            $settings['contents'][$cid]['scripts'] = $this->getCore()->getAssetsUrls($files['scripts']);
            $settings['contents'][$cid]['styles'] = $this->getCore()->getAssetsUrls($files['styles']);
            }
            */

            //$settings = self::get_content_files($settings, $content);
        }

        return $settings;
    }

    public function getContentSettings($id): array
    {
        $lang = config('hh5p.language');

        // READ this https://h5p.org/creating-your-own-h5p-plugin
        $user = Auth::user();

        $config = $this->getConfig();

        $settings = [
            'baseUrl' => $config['domain'],
            'url' => $config['url'],
            'postUserStatistics' => config('hh5p.h5p_track_user'),
            'ajax' => [
                'setFinished' => $config['ajaxSetFinished'], // TODO check if this is working and implement this endpoint
                'contentUserData' => $config['ajaxContentUserData'], // TODO check if this is working  this endpoint
            ],
            'saveFreq' => false,
            'siteUrl' => $config['domain'],
            'l10n' => [
                'H5P' => __('h5p::h5p'),
            ],
            'hubIsEnabled' => config('hh5p.h5p_hub_is_enabled'),
            'crossorigin' => 'anonymous',

        ];

        if ($user) {
            $settings['user'] = [
                "name" => $user->name,
                "mail" => $user->email,
            ];
        }

        $settings['loadedJs'] = [];
        $settings['loadedCss'] = [];

        $settings['core'] = [
            'styles' => [],
            'scripts' => [],
        ];
        foreach (H5PCore::$styles as $style) {
            $settings['core']['styles'][] = $config['get_h5pcore_url'] . '/' . $style;
        }
        foreach (H5PCore::$scripts as $script) {
            $settings['core']['scripts'][] = $config['get_h5pcore_url'] . '/' . $script;
        }
        //$settings['core']['scripts'][] = $config['get_h5peditor_url'].'/language/' . $lang . '.js';

        $h5pEditorDir = file_exists(__DIR__ . '/../../vendor/h5p/h5p-editor')
            ? __DIR__ . '/../../vendor/h5p/h5p-editor'
            : __DIR__ . '/../../../../../vendor/h5p/h5p-editor';
        $h5pCoreDir = file_exists(__DIR__ . '/../../vendor/h5p/h5p-core')
            ? __DIR__ . '/../../vendor/h5p/h5p-core'
            : __DIR__ . '/../../../../../vendor/h5p/h5p-core';

        $settings['core']['scripts'] = $this->margeFileList(
            $settings['core']['scripts'],
            'js',
            [$config['get_h5peditor_url'], $config['get_h5pcore_url']],
            [$h5pEditorDir, $h5pCoreDir]
        );
        /*$settings['core']['styles'] = $this->margeFileList(
            $settings['core']['styles'],
            'css',
            [$config['get_h5peditor_url'], $config['get_h5pcore_url']],
            [$h5pEditorDir, $h5pCoreDir]
        );*/

        // get settings start

        $content = $this->getCore()->loadContent($id);
        $content['metadata']['title'] = $content['title'];

        $safe_parameters = $this->getCore()->filterParameters($content); // TODO: actually this is inserting stuff in Database, it shouldn'e instert anything since this is a GET

        $library = $content['library'];

        $uberName = $library['name'] . ' ' . $library['majorVersion'] . '.' . $library['minorVersion'];

        $settings['contents']["cid-$id"] = [
            'library' => $uberName,
            'content' => $content,
            'jsonContent' => $safe_parameters,
            'fullScreen' => $content['library']['fullscreen'],
            // TODO check all of those endpointis are working fine
            'exportUrl' => config('hh5p.h5p_export') ? route('hh5p.content.export', [$content['id']]) : '',
            //'embedCode'       => '<iframe src="'.route('h5p.embed', ['id' => $content['id']]).'" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
            //'resizeCode'      => '<script src="'.self::get_h5pcore_url('/js/h5p-resizer.js').'" charset="UTF-8"></script>',
            //'url'             => route('h5p.embed', ['id' => $content['id']]),
            'title' => $content['title'],
            'displayOptions' => $this->getCore()->getDisplayOptionsForView(0, $content['id']),
            'contentUserData' => [
                0 => [
                    'state' => '{}', // TODO this should be retrived
                ],
            ],
            'nonce' => $content['nonce'],
        ];

        // get settings stop

        $settings['nonce'] = $settings['contents']["cid-$id"]['nonce'];

        $language_script = '/language/' . $lang . '.js';

        $preloaded_dependencies = $this->getCore()->loadContentDependencies($id, 'preloaded');
        $files = $this->getCore()->getDependenciesFiles($preloaded_dependencies);

        $cid = $settings['contents']["cid-$id"];
        $embed = H5PCore::determineEmbedType($cid['content']['embed_type'] ?? 'div', $cid['content']['library']['embedTypes']);

        $scripts = array_map(function ($value) use ($config) {
            return $config['url'] . ($value->path . $value->version);
        }, $files['scripts']);

        $styles = array_map(function ($value) use ($config) {
            return $config['url'] . ($value->path . $value->version);
        }, $files['styles']);

        if ($embed === 'iframe') {
            $settings['contents']["cid-$id"]['scripts'] = $scripts;
            $settings['contents']["cid-$id"]['styles'] = $styles;
        } else {
            $settings['loadedCss'] = $styles;
            $settings['loadedJs'] = $scripts;
        }

        return $settings;
    }

    public function deleteLibrary($id): bool
    {
        $library = H5pLibrary::findOrFail($id);

        // TODO: check if runnable
        // TODO: check is usable, against content. If yes should ne be deleted

        // Error if in use
        // TODO implement getLibraryUsage, once content is ready
        // $usage = $this->getRepository()->getLibraryUsage($library);
        /*
        if ($usage['content'] !== 0 || $usage['libraries'] !== 0) {
            return redirect()->route('h5p.library.index')
                ->with('error', trans('laravel-h5p.library.used_library_can_not_destoroied'));
        }
        */

        $this->getRepository()->deleteLibrary($library);

        return true;
    }

    public function getSettingsForContent($id)
    {
        $content = $this->getCore()->loadContent($id);
        $content['metadata']['title'] = $content['title'];

        $safe_parameters = $this->getCore()->filterParameters($content); // TODO: actually this is inserting stuff in Database, it shouldn'e instert anything since this is a GET

        $library = $content['library'];

        $uberName = $library['name'] . ' ' . $library['majorVersion'] . '.' . $library['minorVersion'];

        $settings = [
            'library' => $uberName,
            'content' => $content,
            'jsonContent' => json_encode([
                'params' => json_decode($safe_parameters),
                'metadata' => $content['metadata'],
            ]),
            'fullScreen' => $content['library']['fullscreen'],
            'exportUrl' => config('hh5p.h5p_export') ? route('hh5p.content.export', [$content['id']]) : '',
            //'embedCode'       => '<iframe src="'.route('h5p.embed', ['id' => $content['id']]).'" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
            //'resizeCode'      => '<script src="'.self::get_h5pcore_url('/js/h5p-resizer.js').'" charset="UTF-8"></script>',
            //'url'             => route('h5p.embed', ['id' => $content['id']]),
            'title' => $content['title'],
            'displayOptions' => $this->getCore()->getDisplayOptionsForView(0, $content['id']),
            'contentUserData' => [
                0 => [
                    'state' => '{}', // TODO get user actual state
                ],
            ],
            'user' => [
                "name" => "Escola123x! Wojczal",
                "mail" => "mateusz@escolasoft.com"
            ],
            'nonce' => $content['nonce'],
        ];

        return $settings;
    }

    public function uploadFile($contentId, $field, $token, $nonce = null)
    {
        if (!$this->isValidEditorToken($token)) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }

        $file = new H5peditorFile($this->getRepository());
        if (!$file->isLoaded()) {
            throw new H5PException(H5PException::FILE_NOT_FOUND);
        }

        // Make sure file is valid and mark it for cleanup at a later time
        if ($file->validate()) {
            $file_id = $this->getFileStorage()->saveFile($file, $contentId);
            $this->getEditorStorage()->markFileForCleanup($file_id, $nonce);
        }

        $result = json_decode($file->getResult());
        $result->path = $file->getType() . 's/' . $file->getName() . '#tmp';

        return $result;
    }

    private function isValidEditorToken(string $token = null): bool
    {
        return $this->getEditor()->ajaxInterface->validateEditorToken($token);
    }

    private function margeFileList(array $fileList, string $type, array $replaceFrom, array $replaceTo): array
    {
        $newFileList = [];
        foreach ($fileList as $file) {
            $newFileList[] = str_replace($replaceFrom, $replaceTo, $file);
        }
        $margeFiles = new MargeFiles($newFileList, $type, $replaceTo[0]);

        return [$replaceFrom[0] . ($type . '/' . basename($margeFiles->getHashedFile()))];
    }

    /**
     * Gets content type cache for globally available libraries and the order
     * in which they have been used by the author
     *
     * @param bool $cacheOutdated The cache is outdated and not able to update
     */


    private function isContentTypeCacheUpdated()
    {

        // Update content type cache if enabled and too old
        $ct_cache_last_update = $this->core->h5pF->getOption('content_type_cache_updated_at', 0);
        $outdated_cache = $ct_cache_last_update + (60 * 60 * 24 * 7); // 1 week

        if (time() > $outdated_cache) {
            $success = $this->core->updateContentTypeCache();

            $this->core->h5pF->setOption('content_type_cache_updated_at', time());
            if (!$success) {
                return false;
            }
        }
        return true;
    }


    // TODO add annotations and update contract
    // TODO add test
    public function getContentTypeCache(): array
    {

        // https://github.com/Lumieducation/H5P-Nodejs-library/wiki/Communication-with-the-H5P-Hub

        $cacheOutdated = $this->isContentTypeCacheUpdated();

        $canUpdateOrInstall = ($this->core->h5pF->hasPermission(H5PPermission::INSTALL_RECOMMENDED) ||
            $this->core->h5pF->hasPermission(H5PPermission::UPDATE_LIBRARIES));
        return array(
            'outdated' => $cacheOutdated && $canUpdateOrInstall,
            'libraries' => $this->editor->getLatestGlobalLibrariesData(),
            'recentlyUsed' => $this->editor->ajaxInterface->getAuthorsRecentlyUsedLibraries(),
            'apiVersion' => array(
                'major' => H5PCore::$coreApi['majorVersion'],
                'minor' => H5PCore::$coreApi['minorVersion']
            ),
            'details' => $this->core->h5pF->getMessages('info')
        );
    }

    // TODO add annotations and update contract
    // TODO add test
    public function getUpdatedContentHubMetadataCache()
    {
        $lang = config('hh5p.language');
        return $this->core->getUpdatedContentHubMetadataCache($lang);
    }

    // TODO add annotations
    // TODO add test
    private function callHubEndpoint($endpoint)
    {
        $path = $this->core->h5pF->getUploadedH5pPath();
        $response = $this->core->h5pF->fetchExternalData(H5PHubEndpoints::createURL($endpoint), NULL, TRUE, empty($path) ? TRUE : $path);
        if (!$response) {
            throw new H5PException(H5PException::DOWNLOAD_FAILED . ' ' . $this->core->h5pF->getMessages('error'));
            return FALSE;
        }

        return TRUE;
    }

    // TODO add annotations and update contract
    // TODO add test
    public function libraryInstall($machineName)
    {
        // Determine which content type to install from post data
        if (!$machineName) {
            throw new H5PException(H5PException::NO_CONTENT_TYPE);
        }

        // Look up content type to ensure it's valid(and to check permissions)
        $contentType = $this->editor->ajaxInterface->getContentTypeCache($machineName);
        if (!$contentType) {
            throw new H5PException(H5PException::INVALID_CONTENT_TYPE);
        }

        // Check install permissions
        if (!$this->editor->canInstallContentType($contentType)) {
            throw new H5PException(H5PException::INSTALL_DENIED);
        } else {
            // Override core permission check
            $this->core->mayUpdateLibraries(TRUE);
        }

        // Retrieve content type from hub endpoint
        $response = $this->callHubEndpoint(H5PHubEndpoints::CONTENT_TYPES . $machineName);

        if (!$response) return;

        // Session parameters has to be set for validation and saving of packages
        if (!$this->getValidator()->isValidPackage(true)) {
            return;
        }

        $this->getStorage()->savePackage(NULL, NULL, TRUE);

        // Clean up
        $this->getEditorStorage()->removeTemporarilySavedFiles($this->core->h5pF->getUploadedH5pFolderPath());

        // Successfully installed. Refresh content types
        return $this->getContentTypeCache();
    }

    // TODO add annotations and update contract
    // TODO add test
    public function uploadLibrary($token, $file, $contentId)
    {
        $this->validatePackage($file, false, false);
        $this->getStorage()->savePackage(NULL, NULL, TRUE);

        // Make content available to editor
        $this->core->fs->moveContentDirectory($this->core->h5pF->getUploadedH5pFolderPath(), $contentId);

        // Clean up
        $this->getEditorStorage()->removeTemporarilySavedFiles($this->core->h5pF->getUploadedH5pFolderPath());

        return [
            'h5p' => $this->core->mainJsonData,
            'content' => $this->core->contentJsonData,
            'contentTypes' => $this->getContentTypeCache()
        ];
    }
    /**
     * End-point for filter parameter values according to semantics.
     *
     * @param {string} $libraryParameters
     */
    // TODO add test
    public function filterLibraries($libraryParameters)
    {

        $libraryParameters = json_decode($libraryParameters);
        if (!$libraryParameters) {
            throw new H5PException(H5PException::NO_LIBRARY_PARAMETERS);
        }

        // Filter parameters and send back to client
        $this->getContentValidator()->validateLibrary($libraryParameters, (object)array('options' => array($libraryParameters->library)));

        return $libraryParameters;
    }

    public function getTranslations(array $libraries, ?string $language = null): array
    {
        $language = $language ?? config('hh5p.language');

        $libs = [];
        foreach ($libraries as $library) {
            preg_match('/(\d+\.?)+$/', $library, $matches);
            $version = explode(".", $matches[0]);

            $libs[] = [
                'majorVersion' => $version[0] ?? null,
                'minorVersion' => $version[1] ?? null,
                'name' => preg_match('/([^\s]+)/', $library, $matches) ? $matches[0] : null
            ];
        }

        return $this->editorAjaxRepository->getTranslations($libs, $language);
    }
}
