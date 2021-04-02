<?php

namespace EscolaLms\HeadlessH5P\Services;

use Illuminate\Http\UploadedFile;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\HeadlessH5P;
use H5PCore;
use H5peditor;
use H5PEditorEndpoints;

class HeadlessH5PService implements HeadlessH5PServiceContract
{
    public function __construct(HeadlessH5P $h5p)
    {
        $this->h5p = $h5p;
    }

    /** Copy file to `getUploadedH5pPath` and validates its contents */
    public function validatePackage(UploadedFile $file, $skipContent = true, $h5p_upgrade_only = false): bool
    {
        rename($file->getPathName(), $this->h5p->getRepository()->getUploadedH5pPath());
        try {
            $isValid = $this->h5p->getValidator()->isValidPackage($skipContent, $h5p_upgrade_only);
        } catch (Exception $err) {
            var_dump($err);
        }
        return $isValid;
    }

    /**
   * Saves a H5P file
   *
   * @param null $content
   * @param int $contentMainId
   *  The main id for the content we are saving. This is used if the framework
   *  we're integrating with uses content id's and version id's
   * @param bool $skipContent
   * @param array $options
   * @return bool TRUE if one or more libraries were updated
   * TRUE if one or more libraries were updated
   * FALSE otherwise
   */
    public function savePackage(object $content = null, int $contentMainId = null, bool $skipContent = true, array $options = []): bool
    { // this is crazy, it does save package from `getUploadedH5pPath` path
        try {
            $this->h5p->getStorage()->savePackage($content, $contentMainId, $skipContent, $options);
        } catch (Exception $err) {
            return false;
        }
        return true;
    }

    public function getMessages($type = 'error')
    {
        return $this->h5p->getRepository()->getMessages($type);
    }

    public function getLibraries($machineName, $major_version, $minor_version)
    {
        $this->h5p->getEditor()->ajax->action(H5PEditorEndpoints::LIBRARIES);
        return [];

        /*

        $h5p = App::make('LaravelH5p');
        $core = $h5p::$core;
        $editor = $h5p::$h5peditor;

        if ($machineName) {
            $defaultLanguag = $editor->getLibraryLanguage($machineName, $major_version, $minor_version, $h5p->get_language());
            Log::debug('An informational message.'.$machineName.'====='.$h5p->get_language().'====='.$defaultLanguag);

            //   public function getLibraryData($machineName, $majorVersion, $minorVersion, $languageCode, $prefix = '', $fileDir = '', $defaultLanguage) {

            $editor->ajax->action(H5PEditorEndpoints::SINGLE_LIBRARY, $machineName, $major_version, $minor_version, $h5p->get_language(), '', $h5p->get_h5plibrary_url('', true), $defaultLanguag);  //$defaultLanguage

        } else {

            $editor->ajax->action(H5PEditorEndpoints::LIBRARIES);
        }
        */
    }

    public function getEditorSettings($content = null)
    {
        $config = [
            'domain' => 'domain',
            'url' => asset('storage/h5p'),
            'ajaxSetFinished' => 'ajaxSetFinished',
            'ajaxContentUserData' => 'contentUserData',
            'saveFreq' => 'saveFreq',
            'l10n' => 'l10n',
            'filesPath'=> 'filesPath',
            'fileIcon' => 'fileIcon',
            'ajaxPath'=> 'ajaxPath',
            'libraryUrl' => 'libraryUrl',
            'getCopyrightSemantics'=>'getCopyrightSemantics', // self::$contentvalidator->getCopyrightSemantics(),
            'getMetadataSemantics'=>'getMetadataSemantics', // self::$contentvalidator->getCopyrightSemantics(),
            'get_laravelh5p_url' => url('editor'),
            'get_h5peditor_url' => url('h5p-editor'),
            'get_language' => 'en',
            'get_h5pcore_url' => url('h5p-core')
        ];

        $settings =  [
            'baseUrl'            => $config['domain'],
            'url'                => $config['url'],
            'postUserStatistics' => false,
            'ajax'               => [
                'setFinished'     => $config['ajaxSetFinished'],
                'contentUserData' => $config['ajaxContentUserData'],
            ],
            'saveFreq' => false,
            'siteUrl'  => $config['domain'],
            'l10n'     => [
                'H5P' => $config['l10n']
            ],
            'hubIsEnabled' => false,
        ];

        $settings['loadedJs'] = [];
        $settings['loadedCss'] = [];

        $settings['core'] = [
            'styles'  => [],
            'scripts' => [],
        ];

        $settings['core']['styles'][] = $config['get_laravelh5p_url'].'/css/laravel-h5p.css';

        foreach (H5PCore::$styles as $style) {
            $settings['core']['styles'][] = $config['get_h5pcore_url'].'/'.$style;
        }
        foreach (H5PCore::$scripts as $script) {
            $settings['core']['scripts'][] = $config['get_h5pcore_url'].'/'.$script;
        }

        $settings['core']['scripts'][] = $config['get_h5peditor_url'].'/scripts/h5peditor-editor.js';

        $settings['core']['scripts'][] = $config['get_laravelh5p_url'].'/laravel-h5p.js';

        $settings['editor'] = [
            'filesPath' => $config['filesPath'],
            'fileIcon'  => [
                'path'   => $config['fileIcon'],
                'width'  => 50,
                'height' => 50,
            ],
            //'ajaxPath' => route('h5p.ajax').'/?_token=' . $token ,
            'ajaxPath' => $config['ajaxPath'],
            // for checkeditor,
            'libraryUrl'         => $config['libraryUrl'],
            'copyrightSemantics' => $config['getCopyrightSemantics'],
            'metadataSemantics'  => $config['getMetadataSemantics'],
            'assets'             => [],
            'deleteMessage'      => trans('laravel-h5p.content.destoryed'),
            'apiVersion'         => H5PCore::$coreApi,
        ];

        if ($content !== null) {
            $settings['editor']['nodeVersionId'] = $content['id'];
        }

        // load core assets
        $settings['editor']['assets']['css'] = $settings['core']['styles'];
        $settings['editor']['assets']['js'] = $settings['core']['scripts'];

        $settings['editor']['assets']['js'][] = $config['get_laravelh5p_url'] . '/js/laravel-h5p-editor.js';

        // add editor styles
        foreach (H5peditor::$styles as $style) {
            $settings['editor']['assets']['css'][] = $config['get_h5peditor_url']. ('/'.$style);
        }
        // Add editor JavaScript
        foreach (H5peditor::$scripts as $script) {
            // We do not want the creator of the iframe inside the iframe
            if ($script !== 'scripts/h5peditor-editor.js') {
                $settings['editor']['assets']['js'][] = $config['get_h5peditor_url'].('/'.$script);
            }
        }

        $language_script = '/language/'.$config['get_language'].'.js';
        $settings['editor']['assets']['js'][] = $config['get_h5peditor_url'].($language_script);

        if ($content) {
            //$settings = self::get_content_files($settings, $content);
        }
        
        return $settings;
    }
}
