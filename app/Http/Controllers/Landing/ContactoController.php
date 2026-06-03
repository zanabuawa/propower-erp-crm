<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Mail\ContactoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactoController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:120',
            'empresa'  => 'nullable|string|max:120',
            'correo'   => 'required|email|max:120',
            'telefono' => 'nullable|string|max:30',
            'sector'   => 'nullable|string|max:60',
            'mensaje'  => 'required|string|max:2000',
        ]);

        Mail::to('no-reply@marcordgzdev.net')->send(new ContactoMail($data));

        return response()->json(['ok' => true]);
    }
}
