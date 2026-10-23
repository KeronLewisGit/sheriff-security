<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $all = $request->query('show') === 'all';

        return view('admin.messages.index', [
            'messages' => Message::query()->when(! $all, fn ($q) => $q->open())->latest()->paginate(25)->withQueryString(),
            'all' => $all,
        ]);
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        $message->handled_at = $request->boolean('handled') ? now() : null;
        $message->save();

        return back()->with('status', $message->handled_at ? 'Marked as handled.' : 'Moved back to open.');
    }
}
