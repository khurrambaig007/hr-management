@php
    $hrHostLayout = config('hr-management.layout');
    $hrUseHost = is_string($hrHostLayout) && $hrHostLayout !== '' && View::exists($hrHostLayout);
@endphp
@extends($hrUseHost ? 'hr::layouts.host' : 'hr::layouts.standalone')