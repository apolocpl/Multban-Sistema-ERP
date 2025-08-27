<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function applyFilters(Request $request, $filtersList, $nameFilterKey)
    {
        $filters = $request->only($filtersList); // Get specific filter inputs
        session([$nameFilterKey => $filters]); // Store the filters in the session under a key
    }
}
