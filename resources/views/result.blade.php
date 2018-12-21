@extends('layouts.app')

@section('content')
   <h4>Страница результатов</h4>


  -- {{ $id }}<br>
  -- {{ $name }}<br>
  -- {{ $location }}<br>
  -- {{ $description}}<br>
  -- {{ $price}}<br>
  -- {{ $status }}<br>
  -- {{ $rent }}<br>
   -- {{ $weblink }}<br>





   характеристики:<br>



   @foreach($options as $option =>$value)



       ----{{ $option  }}  {{ $value }}<br>


   @endforeach





@endsection
