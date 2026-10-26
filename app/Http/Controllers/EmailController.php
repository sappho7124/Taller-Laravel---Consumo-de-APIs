<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SendGridService;

class EmailController extends Controller
{
    protected SendGridService $sendGridService;

    public function __construct(SendGridService $sendGridService)
    {
        $this->sendGridService = $sendGridService;
    }

    public function index()
    {
        return view('envio_email');
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $success = $this->sendGridService->sendEmail(
            $request->input('email'),
            $request->input('subject'),
            nl2br(e($request->input('message')))
        );

        if ($success) {
            return back()->with('status', '¡Correo electrónico enviado exitosamente vía SendGrid API!');
        }

        return back()->withErrors(['error' => 'No fue posible enviar el correo. Revisa las credenciales de la API en el archivo .env']);
    }
}