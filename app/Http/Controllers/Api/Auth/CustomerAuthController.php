<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth\AuthController;

class CustomerAuthController extends AuthController
{
    protected function getUserType()
    {
        return 'customer';
    }
}
