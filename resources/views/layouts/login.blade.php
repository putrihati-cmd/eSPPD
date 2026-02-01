<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login e-SPPD</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%); font-family: 'Inter', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="flex items-center justify-center min-h-screen">
    {{ $slot ?? '' }}
    @yield('content')
    @livewireScripts
</body>
</html>
