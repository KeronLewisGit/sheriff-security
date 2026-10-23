<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Notifications\NewSubmission;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return to_route('contact')->with('sent', true); // honeypot
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'topic' => ['required', Rule::in(array_keys(Message::TOPICS))],
            'body' => ['required', 'string', 'max:3000'],
        ]);
        $message = Message::create($data);

        Notify::office(new NewSubmission(
            'Website message: '.$message->topicLabel().' from '.$message->name,
            array_filter([$message->name.($message->company ? ', '.$message->company : ''), $message->body]),
            route('admin.messages.index'),
            $message->email,
            $message->name,
        ));

        return to_route('contact')->with('sent', true);
    }
}
