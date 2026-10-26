<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CloudSpace - Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 border border-gray-100">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">CloudSpace Solutions</h1>
            <p class="text-sm text-gray-500">Reserva y Alquiler de Espacios de Coworking</p>
        </div>

        <form id="payment-form">
            <!-- Selección de Monto y Moneda -->
            <div class="mb-4">
                <label for="amount" class="block text-sm font-semibold text-gray-700 mb-1">Monto a Pagar</label>
                <div class="relative">
                    <input type="number" id="amount" name="amount" step="0.01" min="1" value="25.00" required
                           class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="absolute left-3 top-2 text-gray-500 font-bold">$</span>
                </div>
            </div>

            <div class="mb-5">
                <label for="currency" class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Moneda</label>
                <select id="currency" name="currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="usd">USD - Dólares Americanos</option>
                    <option value="eur">EUR - Euros</option>
                </select>
            </div>

            <button type="button" id="init-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition duration-200">
                Continuar con el Pago
            </button>

            <!-- Contenedor Stripe Elements -->
            <div id="stripe-container" class="hidden mt-6 pt-4 border-t border-gray-200">
                <div id="payment-element" class="mb-5"></div>
                <button type="submit" id="submit-btn" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition duration-200">
                    Confirmar Transacción
                </button>
            </div>

            <div id="payment-message" class="hidden mt-4 p-3 rounded-lg text-sm text-center font-medium"></div>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('email.index') }}" class="text-xs text-indigo-600 hover:underline">Ir al módulo de prueba de correo (SendGrid) &rarr;</a>
        </div>
    </div>

    <script>
        const stripe = Stripe("{{ config('services.stripe.key') }}");
        let elements;

        const initBtn = document.getElementById('init-btn');
        const stripeContainer = document.getElementById('stripe-container');
        const paymentForm = document.getElementById('payment-form');
        const paymentMessage = document.getElementById('payment-message');
        const submitBtn = document.getElementById('submit-btn');

        initBtn.addEventListener('click', async () => {
            const amount = document.getElementById('amount').value;
            const currency = document.getElementById('currency').value;

            if (!amount || amount <= 0) {
                alert('Ingrese un monto válido');
                return;
            }

            initBtn.disabled = true;
            initBtn.innerText = 'Inicializando pasarela...';

            try {
                const response = await fetch("{{ route('payment.intent') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ amount, currency })
                });

                const data = await response.json();

                if (data.error) {
                    showMessage(data.error, true);
                    initBtn.disabled = false;
                    initBtn.innerText = 'Continuar con el Pago';
                    return;
                }

                // RF 1.3: Recibir clientSecret y montar Stripe Elements
                elements = stripe.elements({ clientSecret: data.clientSecret });
                const paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');

                stripeContainer.classList.remove('hidden');
                initBtn.classList.add('hidden');

            } catch (err) {
                showMessage('Error al conectar con el servidor', true);
                initBtn.disabled = false;
                initBtn.innerText = 'Continuar con el Pago';
            }
        });

        paymentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerText = 'Procesando pago seguro...';

            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.href,
                },
                redirect: 'if_required'
            });

            if (error) {
                showMessage(error.message, true);
                submitBtn.disabled = false;
                submitBtn.innerText = 'Confirmar Transacción';
            } else {
                showMessage('¡Transacción completada exitosamente! Se procesará el comprobante.', false);
                stripeContainer.classList.add('hidden');
            }
        });

        function showMessage(msg, isError) {
            paymentMessage.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
            paymentMessage.classList.add('border');
            if (isError) {
                paymentMessage.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
            } else {
                paymentMessage.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
            }
            paymentMessage.innerText = msg;
        }
    </script>
</body>
</html>