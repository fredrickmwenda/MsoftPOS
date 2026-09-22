<?php

namespace App\Http\Controllers;

use App\Models\DeleteAccountRequest;
use App\Models\DeleteAccountRequest as ModelsDeleteAccountRequest;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class DeleteAccountRequestController extends Controller
{


    public function show(Request $id)
    {
        $general_setting = GeneralSetting::first();
        $theme = $general_setting->theme ?? 'light';
        
        return view('delete_account', compact('general_setting', 'theme'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function submit(Request $request)
    {
         $request->validate([
            'email' => 'required|email',
            'reason' => 'nullable|string',
        ]);

        ModelsDeleteAccountRequest::create($request->only('email', 'reason'));

        return back()->with('success', 'Your account deletion request has been submitted successfully.');
    }

 
}
