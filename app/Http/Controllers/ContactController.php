<?php
// app/Http/Controllers/ContactController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessage;

class ContactController extends Controller
{
    public function create()
    {
        return view('contact.create');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:120'],
            'email'   => ['required','email','max:190'],
            'subject' => ['required','string','max:150'],
            'message' => ['required','string','max:2000'],
            // simple honeypot
            'website' => ['nullable','size:0'],
        ], [], ['website' => '']); // hide name in errors

        // where to receive messages:
        $to = config('mail.contact_to', config('mail.from.address'));

        try {
            Mail::to($to)->send(new ContactMessage(
                $data['name'], $data['email'], $data['subject'], $data['message']
            ));

            return back()->with('success', 'Thanks! Your message has been sent.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Could not send your message right now.');
        }
    }
}
