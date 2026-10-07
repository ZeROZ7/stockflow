<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'StockFlow') }}</title>
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @fonts
        @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    </head>
    <body class="h-full font-sans text-slate-800 antialiased">
        <div id="app" class="h-full"></div>
    </body>
</html>
