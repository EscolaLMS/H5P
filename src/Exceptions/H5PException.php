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
}
