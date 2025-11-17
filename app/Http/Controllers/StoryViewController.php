<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryViewController extends Controller
{
    public function show($id)
    {
        $story_id = intval($id);

        if (!$story_id) {
            return redirect()->route('browse');
        }

        return view('stories', [
            'story_id' => $story_id
        ]);
    }
}