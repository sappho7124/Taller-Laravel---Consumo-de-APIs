<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudSpace - Notificaciones SendGrid</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg border border-gray-100">
        <h1 class="text-2xl font-bold mb-2 text-gray-800 text-center">Notificaciones Transaccionales</h1>
        <p class="text-sm text-gray-500 text-center mb-6">Módulo de prueba API SendGrid</p>

        @if (session('status'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg mb-4 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('email.send') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Destinatario</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="usuario@correo.com" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Asunto</label>
                <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Confirmación de Reserva" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mensaje (HTML permitido)</label>
                <textarea name="message" rows="4" required placeholder="Escriba el cuerpo del mensaje..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition duration-200">
                Enviar Correo
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('checkout.index') }}" class="text-xs text-indigo-600 hover:underline">&larr; Volver a la pasarela de pagos</a>
        </div>
    </div>

</body>
</html>