<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $tags = Tag::withCount('customers')->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'color' => 'required|string|max:7',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Tag::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Tag created successfully.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }

    public function attach(Request $request)
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'taggable_type' => 'required|string|in:App\\Models\\Customer,App\\Models\\Deal',
            'taggable_id' => 'required|integer',
        ]);

        $class = $validated['taggable_type'];
        $model = $class::findOrFail($validated['taggable_id']);

        $model->tags()->syncWithoutDetaching([$validated['tag_id']]);

        return back()->with('success', 'Tag attached.');
    }

    public function detach(Request $request)
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
            'taggable_type' => 'required|string|in:App\\Models\\Customer,App\\Models\\Deal',
            'taggable_id' => 'required|integer',
        ]);

        $class = $validated['taggable_type'];
        $model = $class::findOrFail($validated['taggable_id']);

        $model->tags()->detach($validated['tag_id']);

        return back()->with('success', 'Tag removed.');
    }
}
