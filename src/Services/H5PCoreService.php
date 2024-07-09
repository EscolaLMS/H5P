<?php

namespace EscolaLms\HeadlessH5P\Services;

use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PFrameworkInterface;
use H5PContentValidator;
use H5PCore;
use Illuminate\Support\Facades\Log;

class H5PCoreService extends H5PCore
{
    protected bool $exportEnabled;

    public function __construct(H5PFrameworkInterface $H5PFramework, $path, $url, $language = 'en', $export = FALSE)
    {
        parent::__construct($H5PFramework, $path, $url, $language, $export);

        $this->exportEnabled = $export;
    }

    /**
     * @param $content
     * @return false|mixed|Object|string|null
     */
    public function filterParameters(&$content)
    {
        if (!empty($content['filtered']) &&
            (!$this->exportEnabled ||
                ($content['slug'] &&
                    $this->fs->hasExport($content['slug'] . '-' . $content['id'] . '.h5p')))) {
            return $content['filtered'];
        }

        if (!(isset($content['library']) && isset($content['params']))) {
            return NULL;
        }

        // Validate and filter against main library semantics.
        $validator = new H5PContentValidator($this->h5pF, $this);
        $params = (object) array(
            'library' => H5PCore::libraryToString($content['library']),
            'params' => json_decode($content['params'])
        );
        if (!$params->params) {
            return NULL;
        }
        $validator->validateLibrary($params, (object) array('options' => array($params->library)));

        // Handle addons:
        $addons = $this->h5pF->loadAddons();
        foreach ($addons as $addon) {
            $add_to = json_decode($addon['addTo']);

            if (isset($add_to->content->types)) {
                foreach($add_to->content->types as $type) {

                    if (isset($type->text->regex) &&
                        $this->textAddonMatches($params->params, $type->text->regex)) {
                        $validator->addon($addon);

                        // An addon shall only be added once
                        break;
                    }
                }
            }
        }

        $params = json_encode($params->params);

        // Update content dependencies.
        $content['dependencies'] = $validator->getDependencies();

        // Sometimes the parameters are filtered before content has been created
        if ($content['id']) {
            $this->h5pF->deleteLibraryUsage($content['id']);
            $this->h5pF->saveLibraryUsage($content['id'], $content['dependencies']);

            if (!$content['slug']) {
                $content['slug'] = $this->generateContentSlug($content);

                // Remove old export file
                $this->fs->deleteExport($content['id'] . '.h5p');
            }

            if ($this->exportEnabled) {
                // Recreate export file
                $exporter = new H5PExportService($this->h5pF, $this);
                $content['filtered'] = $params;
                $exporter->createExportFile($content);
            }

            // Cache.
            $this->h5pF->updateContentFields($content['id'], array(
                'filtered' => $params,
                'slug' => $content['slug']
            ));
        }
        return $params;
    }

    private function textAddonMatches($params, $pattern, $found = false) {
        $type = gettype($params);
        if ($type === 'string') {
            if (preg_match($pattern, $params) === 1) {
                return true;
            }
        }
        elseif ($type === 'array' || $type === 'object') {
            foreach ($params as $value) {
                $found = $this->textAddonMatches($value, $pattern, $found);
                if ($found === true) {
                    return true;
                }
            }
        }
        return false;
    }

    private function generateContentSlug($content) {
        $slug = H5PCore::slugify($content['title']);

        $available = NULL;
        while (!$available) {
            if ($available === FALSE) {
                // If not available, add number suffix.
                $matches = array();
                if (preg_match('/(.+-)([0-9]+)$/', $slug, $matches)) {
                    $slug = $matches[1] . (intval($matches[2]) + 1);
                }
                else {
                    $slug .=  '-2';
                }
            }
            $available = $this->h5pF->isContentSlugAvailable($slug);
        }

        return $slug;
    }

    public function getLibraryId($library, $libString = NULL)
    {
        static $libraryIdMap = [];

        if (!$libString) {
            $libString = self::libraryToString($library);
        }

        if (!isset($libraryIdMap[$libString]) || !$this->h5pF->checkLibraryById($libraryIdMap[$libString])) {
            $libraryIdMap[$libString] = $this->h5pF->getLibraryId($library['machineName'], $library['majorVersion'], $library['minorVersion']);
        }

        return $libraryIdMap[$libString];
    }
}
