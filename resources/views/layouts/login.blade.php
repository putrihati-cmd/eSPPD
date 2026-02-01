<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login e-SPPD | UIN SAIZU</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            min-height: 100vh; 
            background: #0a0f0d; 
            font-family: 'Outfit', sans-serif; 
            overflow-x: hidden;
        }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot ?? '' }}
    @yield('content')
    @livewireScripts
</body>
</html>
