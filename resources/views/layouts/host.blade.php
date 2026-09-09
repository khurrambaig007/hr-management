@extends(config('hr-management.layout', 'layouts.app'))

@section('content')
    <style>
        .topnav { display:flex; align-items:center; gap:.25rem; flex-wrap:wrap; background:#111827; padding:0 1rem; }
        .topnav a { color:#d1d5db; text-decoration:none; padding:.7rem .6rem; font-size:.9rem; display:inline-block; }
        .topnav a:hover { color:#fff; }
        .brand { font-weight:700; color:#fff; margin-right:.75rem; }
        .spacer { flex:1; }
        .container { max-width:1100px; margin:1.5rem auto; padding:0 1rem; }
        .card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:1.25rem; margin-bottom:1rem; }
        h1 { font-size:1.4rem; margin:0 0 1rem; }
        h2 { font-size:1.1rem; margin:0 0 .75rem; }
        .grid { display:grid; gap:1rem; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); }
        .stat { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:1rem; }
        .stat .num { font-size:1.8rem; font-weight:700; }
        .stat .label { color:#6b7280; font-size:.85rem; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:.55rem .6rem; border-bottom:1px solid #e5e7eb; font-size:.9rem; vertical-align:middle; }
        th { background:#f9fafb; }
        .btn { display:inline-block; padding:.45rem .85rem; border-radius:6px; border:1px solid transparent; cursor:pointer; font-size:.875rem; text-decoration:none; background:#2563eb; color:#fff; }
        .btn-sm { padding:.25rem .6rem; font-size:.8rem; }
        .btn-green { background:#059669; }
        .btn-gray { background:#6b7280; }
        .btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
        .form-group { margin-bottom:1rem; }
        label { display:block; font-weight:600; margin-bottom:.3rem; font-size:.875rem; }
        input, select, textarea { width:100%; padding:.5rem .6rem; border:1px solid #d1d5db; border-radius:6px; font-size:.9rem; background:#fff; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .alert { padding:.75rem 1rem; border-radius:6px; margin-bottom:1rem; font-size:.9rem; }
        .alert-success { background:#d1fae5; color:#065f46; }
        .alert-error { background:#fee2e2; color:#991b1b; }
        .badge { display:inline-block; padding:.15rem .55rem; border-radius:999px; font-size:.75rem; font-weight:600; }
        .badge-green { background:#d1fae5; color:#065f46; }
        .badge-red { background:#fee2e2; color:#991b1b; }
        .badge-yellow { background:#fef3c7; color:#92400e; }
        .badge-blue { background:#dbeafe; color:#1e40af; }
        .badge-gray { background:#e5e7eb; color:#374151; }
        .progress { background:#e5e7eb; border-radius:999px; height:10px; overflow:hidden; }
        .progress-inner { height:100%; background:#2563eb; }
        .muted { color:#6b7280; font-size:.85rem; }
        .text-right { text-align:right; }
        .mb-0 { margin-bottom:0; }
        .mt { margin-top:1rem; }
        .paginator { margin-top:1rem; }
        .paginator a { margin-right:.25rem; }
        .actions { display:flex; gap:.35rem; flex-wrap:wrap; }
    </style>
    @include('hr::layouts.components.frame')
    @stack('styles')
    @stack('scripts')
@endsection