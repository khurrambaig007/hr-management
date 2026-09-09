<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HR Management')</title>
    <style>
        :root { --blue: #2563eb; --green: #059669; --red: #dc2626; --ink: #1f2937; --muted: #6b7280; --line: #e5e7eb; }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; margin: 0; background: #f3f4f6; color: var(--ink); }
        .topnav { background: #111827; color: #fff; padding: 0 1rem; display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
        .topnav a { color: #d1d5db; text-decoration: none; padding: 0.9rem 0.75rem; display: inline-block; font-size: 0.9rem; }
        .topnav a:hover { color: #fff; }
        .brand { font-weight: 700; color: #fff; margin-right: 0.75rem; font-size: 1rem; }
        .spacer { flex: 1; }
        .container { max-width: 1100px; margin: 1.5rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08); padding: 1.25rem; margin-bottom: 1rem; }
        h1 { font-size: 1.4rem; margin: 0 0 1rem; }
        h2 { font-size: 1.1rem; margin: 0 0 0.75rem; }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .stat { background: #fff; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .stat .num { font-size: 1.8rem; font-weight: 700; }
        .stat .label { color: var(--muted); font-size: 0.85rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.55rem 0.6rem; border-bottom: 1px solid var(--line); font-size: 0.9rem; vertical-align: middle; }
        th { background: #f9fafb; }
        .btn { display: inline-block; padding: 0.45rem 0.85rem; border-radius: 6px; border: 1px solid transparent; cursor: pointer; font-size: 0.875rem; text-decoration: none; background: var(--blue); color: #fff; }
        .btn:hover { opacity: 0.9; }
        .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.8rem; }
        .btn-green { background: var(--green); }
        .btn-gray { background: #6b7280; }
        .btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.875rem; }
        input, select, textarea { width: 100%; padding: 0.5rem 0.6rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.9rem; background: #fff; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .badge { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #e5e7eb; color: #374151; }
        .progress { background: #e5e7eb; border-radius: 999px; height: 10px; overflow: hidden; }
        .progress-inner { height: 100%; background: var(--blue); }
        .muted { color: var(--muted); font-size: 0.85rem; }
        .text-right { text-align: right; }
        .mb-0 { margin-bottom: 0; }
        .mt { margin-top: 1rem; }
        .paginator { margin-top: 1rem; }
        .paginator a { margin-right: 0.25rem; }
        .actions { display: flex; gap: 0.35rem; flex-wrap: wrap; }
    </style>
    @stack('styles')
</head>
<body>
    @include('hr::layouts.components.frame')
    @stack('scripts')
</body>
</html>