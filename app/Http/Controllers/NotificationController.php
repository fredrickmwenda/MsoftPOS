<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SendNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasPermissionTo('all_notification')) {
            $lims_notification_all = DB::table('notifications')->get();
            return view('backend.notification.index', compact('lims_notification_all'));
        }

        return redirect()->back()
            ->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        // 1. Handle optional file upload first so we know its name before building payload.
        $documentName = null;
        $document = $request->file('document');

        if ($document) {
            $validator = Validator::make(
                ['extension' => strtolower($document->getClientOriginalExtension())],
                ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
            );

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors());
            }

            $documentName = date('Ymdhis') . '.' . $document->getClientOriginalExtension();
            $document->move('public/documents/notification', $documentName);
        }

        // 2. Resolve the recipient.
        $user = User::find($request->input('receiver_id'));

        if (!$user) {
            return redirect()->back()->withErrors(['receiver_id' => 'Recipient not found.']);
        }

        // 3. Assemble the array payload matching SendNotification::__construct(array $payload).
        $payload = [
            'sender_id'     => Auth::id(),
            'receiver_id'   => $request->input('receiver_id'),
            'reminder_date' => $request->input('reminder_date'),
            'document_name' => $documentName,
            'message'       => $request->input('message', ''),
        ];

        // 4. Dispatch — SendNotification now receives a properly typed array.
        $user->notify(new SendNotification($payload));

        return redirect()->back()->with('message', 'Notification send successfully');
    }

    public function markAsRead()
    {
        Auth::user()
            ->unreadNotifications
            ->where('data.reminder_date', date('Y-m-d'))
            ->markAsRead();

        return response()->json(['success' => true]);
    }
}