<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request, string $locale): RedirectResponse
    {
        $noNewlines = 'regex:/^[^\r\n]*$/';

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:120', $noNewlines],
            'azienda' => ['nullable', 'string', 'max:120', $noNewlines],
            'email' => ['required', 'email:rfc', 'max:160', $noNewlines],
            'telefono' => ['nullable', 'string', 'max:40', $noNewlines],
            'messaggio' => ['required', 'string', 'max:5000'],
            'consenso' => ['accepted'],
            'sito_web' => ['nullable', 'string'],
        ], trans('site.contact.errors'));

        // Campo trappola: gli utenti veri non lo vedono né lo compilano.
        if (! empty($data['sito_web'])) {
            return back()->with('status', __('site.contact.sent'));
        }

        try {
            Mail::send(new ContactMessage($data, $locale));
        } catch (\Throwable $e) {
            // Posta non raggiungibile: il messaggio non si perde (resta nel registro) e il visitatore è avvisato.
            Log::error('Messaggio dal modulo contatto non inviato: '.$e->getMessage(), $data);

            return back()->withInput()->with('error', __('site.contact.failed'));
        }

        return back()->with('status', __('site.contact.sent'));
    }
}
