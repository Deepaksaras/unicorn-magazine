<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display search results.
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        // Placeholder - will be implemented in Phase 2
        return view('search', ['query' => $query]);
    }
}
