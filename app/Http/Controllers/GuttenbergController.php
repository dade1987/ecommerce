<?php

namespace App\Http\Controllers;

use App\Models\GuttenbergPage;
use Illuminate\Http\Request;

class GuttenbergController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $id, Request $request)
    {
        $page = GuttenbergPage::find($id);

        return view('guttenberg-example', compact('page'));
    }
}
