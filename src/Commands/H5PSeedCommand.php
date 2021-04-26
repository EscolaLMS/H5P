<?php

namespace EscolaLms\HeadlessH5P\Commands;

use Illuminate\Console\Command;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

class H5PSeedCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'h5p:seed {--addContent : Should content be added or just the library}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database and files with most of h5p libraries and content. Fetched from h5p.org';

    private HeadlessH5PServiceContract $hh5pService;


    private function downloadAndSeed($lib, $addContent = false)
    {
        // eg https://h5p.org/sites/default/files/h5p/exports/interactive-video-2-618.h5p
        $url = "https://h5p.org/sites/default/files/h5p/exports/$lib";

        $filename = storage_path("app/h5p/temp/$lib");

        $dirname = dirname($filename);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        if (!is_file($filename)) {
            if (file_put_contents($filename, file_get_contents($url))) {
                echo "File downloaded from $url \n";
            } else {
                echo "File downloading failed. \n";
                return;
            }
        }

        copy($filename, $this->hh5pService->getRepository()->getUploadedH5pPath());

        // Content update is skipped because it is new registration
        $content = null;
        $skipContent = !$addContent;
        $h5p_upgrade_only = false;

        if ($this->hh5pService->getValidator()->isValidPackage($skipContent, $h5p_upgrade_only)) {
            $this->hh5pService->getStorage()->savePackage($content, null, $skipContent);
        } else {
            echo "Invalid package $filename \n";
        }

        @unlink($this->hh5pService->getRepository()->getUploadedH5pPath());

        return true;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(HeadlessH5PServiceContract $hh5pService)
    {
        $addContent = $this->option('addContent');

        $this->hh5pService = $hh5pService;
        $libs = [
            "example-content-arts-of-europe-443085.h5p",
            "advanced-blanks-example-1-503253.h5p",
            "course-presentation-21-21180.h5p",
            "interactive-video-2-618.h5p",
            "example-content-virtual-tour-360-441814.h5p",
            "true-false-question-34806.h5p",
            "timeline-3-716.h5p",
            "summary-714.h5p",
            "speak-the-words-set-example-120784.h5p",
            "speak-the-words-73595.h5p",
            "example-content-single-choice-set-64682.h5p",
            "question-set-616.h5p",
            "questionnaire-4-30615.h5p",
            "personality-quiz-21254.h5p",
            "multiple-choice-713.h5p",
            "memory-game-5-708.h5p",
            "mark-the-words-2-1408.h5p",
            "contact-18-1022298.h5p",
            "berries-28-441940.h5p",
            "impressive-presentation-7141.h5p",
            "image-slider-2-130336.h5p",
            "image-sequencing-3-110117.h5p",
            "example-content-image-pairing-2-233382.h5p",
            "image-juxtaposition-65047.h5p",
            "image-hotspots-2-825.h5p",
            "iframe-embedder-621.h5p",
            "image-multiple-hotspot-question-65081.h5p",
            "find-the-hotspot-3024.h5p",
            "example-content-find-the-words-557697.h5p",
            "flashcards-51-111820.h5p",
            "guess-the-answer-2402.h5p",
            "fill-in-the-blanks-837.h5p",
            "essay-4-166755.h5p",
            "drag-the-words-1399.h5p",
            "drag-and-drop-712.h5p",
            "documentation-tool-3022.h5p",
            "chart-7143.h5p",
            "collage-3065.h5p",
            "h5p-column-34794.h5p",
            "dialog-cards-620.h5p",
            "dictation-389727.h5p",
            "audio-recorder-87-748022.h5p",
            "arithmetic-quiz-22-57860.h5p",
            "agamotto-80158.h5p",
            "advent-blue-snowman-1075566.h5p",
            "accordion-6-7138.h5p"
        ];

        foreach ($libs as $lib) {
            echo "seeding $lib \n";
            $this->downloadAndSeed($lib, $addContent);
        }
    }
}
