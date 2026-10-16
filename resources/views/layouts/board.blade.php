<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Display board' }} · {{ config('app.name') }}</title>
    <style>
        :root {
            --bg: #0f172a; --panel: #1e293b; --text: #f1f5f9; --muted: #94a3b8;
            --in: #22c55e; --out: #ef4444; --late: #f59e0b; --line: #334155;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font: 16px/1.4 system-ui, sans-serif; }
        header { display: flex; justify-content: space-between; align-items: baseline; padding: 16px 24px; border-bottom: 1px solid var(--line); }
        header h1 { margin: 0; font-size: 1.5rem; }
        header .clock { font-size: 1.5rem; font-variant-numeric: tabular-nums; color: var(--muted); }
        main { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; padding: 16px 24px; }
        section { background: var(--panel); border-radius: 12px; padding: 16px; }
        section h2 { margin: 0 0 4px; font-size: 1.25rem; }
        section .summary { margin: 0 0 12px; color: var(--muted); font-size: .9rem; }
        ul { list-style: none; margin: 0; padding: 0; }
        li { display: grid; grid-template-columns: 12px 1fr auto; gap: 10px; align-items: start; padding: 8px 0; border-top: 1px solid var(--line); }
        .dot { width: 12px; height: 12px; border-radius: 50%; margin-top: 5px; background: var(--in); }
        .out .dot { background: var(--out); }
        .late .dot { background: var(--late); }
        .detail { color: var(--muted); font-size: .85rem; }
        .time { color: var(--muted); font-size: .85rem; text-align: right; white-space: nowrap; }
        .empty { color: var(--muted); }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
