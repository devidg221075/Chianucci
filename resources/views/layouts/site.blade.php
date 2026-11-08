<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CHIANUCCI MAURIZIO')</title>
    @vite('resources/css/site.css')
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-bianco border-b border-oro/40">
        <a href="/" class="inline-block p-4"><img src="{{ asset('brand/chianucci.svg') }}" alt="CHIANUCCI MAURIZIO" class="h-12"></a>
    </header>
    <main class="flex-1">@yield('content')</main>
</body>
</html>
