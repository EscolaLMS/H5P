<?php

namespace EscolaLms\HeadlessH5P\Exceptions;

use Exception;

class H5PException extends Exception
{
    const INVALID_PARAMETERS_JSON = 'Invalid Json Paramaters';
    const LIBRARY_NOT_FOUND = 'Library not found';
    const CONTENT_NOT_FOUND = 'Content not found';
    const INVALID_FILE_TOKEN = 'Invalid file token';
    const FILE_NOT_FOUND = 'File not found on the server';
    const FILE_INVALID = 'Invalid h5p file';
    const NO_CONTENT_TYPE = 'No content type was specified.';
    const INVALID_CONTENT_TYPE = 'The chosen content type is invalid.';
    const INSTALL_DENIED = 'You do not have permission to install content types. Contact the administrator of your site.';
    const DOWNLOAD_FAILED = 'Failed to download the requested H5P.';
    const NO_LIBRARY_PARAMETERS = 'Could not parse post data.';
}
