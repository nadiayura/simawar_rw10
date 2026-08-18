<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Diperbarui</title>
    @vite('resources/css/app.css')
    <style>
        .checkmark {
            width: 120px; height: 120px; 
            border-radius: 50%;
            background: #22c55e; position: relative; margin: 0 0 20px 0;
            box-shadow: 0 10px 30px rgba(34,197,94,.4);
            animation: pop .4s ease-out;
            display: flex; align-items: center; justify-content: center;
        }

        .flex-checkmark{
            display: flex;
            justify-content: center;
        }
        
        .checkmark svg { width: 64px; height: 64px; }
        .checkmark svg path { stroke: #fff; stroke-width: 6; fill: none; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 100; stroke-dashoffset: 100; animation: draw .6s ease .2s forwards; }
        @keyframes pop { from { transform: scale(.7); opacity: .5 } to { transform: scale(1); opacity: 1 } }
        @keyframes draw { to { stroke-dashoffset: 0 } }
        .pulse { animation: pulse 1.6s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.7) } 70% { box-shadow: 0 0 0 20px rgba(34,197,94,0) } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0) } }
    </style>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 text-center">
        @if($updated)
            <div class="flex-checkmark">
                <div class="checkmark pulse">
                    <svg viewBox="0 0 64 64">
                        <path d="M20 34 L28 42 L44 22"/>
                    </svg>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Pembayaran berhasil diperbarui</h1>
            <p class="text-gray-600 mb-6">Order ID: <span class="font-semibold">{{ $order_id }}</span></p>
            <div class="grid grid-cols-2 gap-3 text-sm mb-6">
                <div class="bg-green-50 text-green-700 rounded-lg p-3">
                    Status: <span class="font-semibold">{{ $transaction_status ?? '-' }}</span>
                </div>
                <div class="bg-blue-50 text-blue-700 rounded-lg p-3">
                    Metode: <span class="font-semibold">{{ $payment_type ?? '-' }}</span>
                </div>
            </div>
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold">Kembali</a>
        @else
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-red-500 text-white flex items-center justify-center text-4xl">!</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Gagal memperbarui</h1>
            <p class="text-gray-600 mb-6">Periksa kembali parameter pemanggilan.</p>
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-gray-800 hover:bg-black text-white font-semibold">Kembali</a>
        @endif
    </div>
</body>
</html>