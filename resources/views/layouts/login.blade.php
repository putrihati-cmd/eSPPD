<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-SPPD UIN SAIZU Purwokerto - Sistem Perjalanan Dinas</title>
    <meta name="description" content="Sistem Informasi Perjalanan Dinas UIN Saizu Purwokerto" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    @livewireStyles
</head>
<body class="antialiased">
    {{ $slot ?? '' }}
    @yield('content')
    @livewireScripts
</body>
</html>
