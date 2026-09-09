@include('hr::layouts.components.nav')
<main class="container">
    @include('hr::layouts.components.alert')
    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
    @yield('hrcontent')
</main>