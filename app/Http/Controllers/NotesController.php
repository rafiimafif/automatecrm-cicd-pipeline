<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'notable_type' => 'required|string|in:App\\Models\\Customer,App\\Models\\Deal',
            'notable_id' => 'required|integer',
            'type' => 'required|in:call,email,meeting,note',
            'content' => 'required|string|max:5000',
        ]);

        $validated['user_id'] = Auth::id();

        Note::create($validated);

        return back()->with('success', 'Note added successfully.');
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
