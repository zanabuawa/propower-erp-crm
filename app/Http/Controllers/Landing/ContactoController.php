<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
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

        $body = implode("\n", array_filter([
            "Nombre:   {$data['nombre']}",
            isset($data['empresa'])  ? "Empresa:  {$data['empresa']}"  : null,
            "Correo:   {$data['correo']}",
            isset($data['telefono']) ? "Teléfono: {$data['telefono']}" : null,
            isset($data['sector'])   ? "Sector:   {$data['sector']}"   : null,
            "",
            $data['mensaje'],
        ]));

        Mail::raw($body, function ($msg) use ($data) {
            $msg->to('contacto@propower.mx')
                ->replyTo($data['correo'], $data['nombre'])
                ->subject("Nuevo contacto web: {$data['nombre']}");
        });

        return response()->json(['ok' => true]);
    }
}
