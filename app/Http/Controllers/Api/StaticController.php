<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaticController extends Controller
{
    public function __invoke()
    {
        return response()->json('Hi, I am a static controller');
    }
}
