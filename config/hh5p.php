<?php
return [
    'domain' => 'domain',
    'url' => 'h5p',
    'ajaxSetFinished' => 'ajaxSetFinished',
    'ajaxContentUserData' => 'contentUserData',
    'saveFreq' => 'saveFreq',
    'l10n' => 'l10n',
    'filesPath' => 'filesPath',
    'fileIcon' => 'fileIcon',
    'ajaxPath' => 'hh5p.index',
    'libraryUrl' => 'h5p-editor',
    'get_laravelh5p_url' => 'editor',
    'get_h5peditor_url' => 'h5p-editor',
    'get_language' => 'en',
    'language' => 'en',
    'get_h5pcore_url' => 'h5p-core',

    // Content screen setting

    'h5p_show_display_option'    => true,
    'h5p_frame'                  => true,
    'h5p_export'                 => false,
    'h5p_embed'                  => false,
    'h5p_copyright'              => false,
    'h5p_icon'                   => false,
    'h5p_track_user'             => false,
    'h5p_ext_communication'      => true,
    'h5p_save_content_state'     => true,
    'h5p_save_content_frequency' => 30,
    'h5p_site_key'               => [
        'h5p_h5p_site_uuid' => false,
    ],
    //'h5p_content_type_cache_updated_at' => 0,
    'h5p_check_h5p_requirements'        => false,
    'h5p_hub_is_enabled'                => true,
    'h5p_version'                       => '1.23.0',

    'guzzle'                            => [],

    'h5p_storage_path'                  => 'app/h5p',
    'h5p_content_storage_path'          => 'app/h5p/content/',
    'h5p_library_url'                   => 'h5p/libraries'
];
