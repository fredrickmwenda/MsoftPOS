<?php

namespace App\Http\Controllers;

use App\Models\AiAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AIAssistantController extends Controller
{
       /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $theme = env('THEME', 'default');
        if (Schema::hasTable('general_settings')) {
            $theme = \App\Models\GeneralSetting::latest()->value('theme');
        }
        $languages = \App\Models\Language::orderBy('code')->get();
        return view('backend.ai.index', compact('theme', 'languages'));
    }
}
