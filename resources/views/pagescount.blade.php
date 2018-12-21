@extends('layouts.app')

@section('content')
    <h4>Pages</h4>

    Пропарсено страниц= {{ $parsingcount }} <br>

    @isset($listOffers)

        @foreach($listOffers as $offer)

            {{ $offer }} <br>

            @endforeach

    @endisset

    Пропарсено страниц= {{ $parsingcount }}








@endsection
