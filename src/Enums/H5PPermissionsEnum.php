<?php

namespace EscolaLms\HeadlessH5P\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class H5PPermissionsEnum extends BasicEnum
{
    const H5P_LIST = 'h5p_list';
    const H5P_AUTHOR_LIST = 'h5p_author_list';
    const H5P_READ = 'h5p_read';
    const H5P_CREATE = 'h5p_create';
    const H5P_DELETE = 'h5p_delete';
    const H5P_UPDATE = 'h5p_update';

    const H5P_LIBRARY_LIST = 'h5p_library_list';
    const H5P_LIBRARY_READ = 'h5p_library_read';
    const H5P_LIBRARY_CREATE = 'h5p_library_create';
    const H5P_LIBRARY_DELETE = 'h5p_library_delete';
    const H5P_LIBRARY_UPDATE = 'h5p_library_update';
    const H5P_LIBRARY_INSTALL = 'h5p_library_install';
    const H5P_LIBRARY_UPLOAD = 'h5p_library_upload';
}
