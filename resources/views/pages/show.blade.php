@extends('layouts.site')

@section('title', ($page->meta_title ?: $page->title).' - CHIANUCCI MAURIZIO')

@push('head')
    @if ($page->css)
        <style>{!! $page->css !!}</style>
    @endif
@endpush

@section('content')
    {!! str_replace('<div id="categorie-home"></div>', view('home.categories')->render(), $page->html) !!}
    @if ($isContact ?? false)
        @include('contact.form')
    @endif
@endsection
