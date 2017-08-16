@extends('layouts.base')

@section('P__title', 'RSI.im - Star Citizen Wiki Short URL Service')

@section('head__content')
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('/media/images/rsi_im/favicon.ico') }}">
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/css/rsi_im/app.css') }}">
@endsection

@section('body__after')
    <script src="{{ URL::asset('/js/app.js') }}"></script>
@endsection