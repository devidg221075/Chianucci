@extends('layouts.site')

@section('title', ($page->meta_title ?: $page->title).' - CHIANUCCI MAURIZIO')

@push('head')
    @if ($page->css)
        <style>{!! $page->css !!}</style>
    @endif
@endpush

@section('content')
    {!! $page->html !!}
@endsection
