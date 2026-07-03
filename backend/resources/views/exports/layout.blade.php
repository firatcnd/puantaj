<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 10px; color: #1e2019; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .meta { color: #587b7f; font-size: 9px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #393e41; color: #d3d0cb; text-align: left; padding: 6px 8px; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #d3d0cb; }
        tr:nth-child(even) td { background-color: #f6f5f3; }
        .text-end { text-align: right; }
        .badge { color: #587b7f; font-weight: bold; }
    </style>
</head>
<body>
    <h1>@yield('title')</h1>
    <div class="meta">
        Puantaj Sistemi — {{ now()->format('d.m.Y H:i') }} tarihinde oluşturuldu. Kayıt sayısı: @yield('count')
    </div>
    @yield('table')
</body>
</html>
