<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakingNews;
use Illuminate\Http\Request;

class BreakingNewsController extends Controller
{
    public function index()
    {
        $items = BreakingNews::where('status', '!=', 4)->orderBy('position')->orderByDesc('id')->paginate(30);

        return view('admin.breaking-news.index', compact('items'));
    }

    public function create()
    {
        $next = (int) BreakingNews::where('status', '!=', 4)->max('position') + 1;

        return view('admin.breaking-news.form', ['item' => new BreakingNews(['status' => 1, 'position' => $next])]);
    }

    public function store(Request $request)
    {
        BreakingNews::create($this->validated($request));

        return redirect()->route('admin.breaking-news.index')->with('success', 'Headline added.');
    }

    public function edit(BreakingNews $breakingNews)
    {
        return view('admin.breaking-news.form', ['item' => $breakingNews]);
    }

    public function update(Request $request, BreakingNews $breakingNews)
    {
        $breakingNews->update($this->validated($request));

        return redirect()->route('admin.breaking-news.index')->with('success', 'Headline updated.');
    }

    public function destroy(BreakingNews $breakingNews)
    {
        $breakingNews->update(['status' => 4]);

        return redirect()->route('admin.breaking-news.index')->with('success', 'Headline deleted.');
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('ids', []) as $i => $id) {
            BreakingNews::whereKey($id)->update(['position' => $i + 1]);
        }

        return response()->json(['message' => 'Order saved.']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:191',
            'url' => 'nullable|string|max:191',
            'position' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]) + ['position' => 0];
    }
}
