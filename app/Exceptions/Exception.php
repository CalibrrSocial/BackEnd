<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Exception extends ExceptionHandler
{
    const GET_ALL_DATA = 'Get all data.';
    const CREATE_SUCCESS = 'Create successfully.';
    const SHOW = 'Show information.';
    const UPDATE_SUCCESS = 'Update successfully.';
    const DELETE_SUCCESS = 'Delete successfully.';
    const PAGE_NOT_FOUND = 'Page not found.';
    const ACCESS_DENIED = 'Access denied';
}
