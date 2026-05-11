@extends('layout.main')
@section('title', 'Home Page')
@section('content')
    <div class="home">
        <h1>Home Page</h1>

        <div class="jobs grid grid-cols-2 gap-4">
            @foreach ($items as $item)
                <x-card-component title="{{ $item['title'] }}" content="{{ $item['content'] }}" imgUrl="{{$item['imgUrl']}}"/>
            @endforeach
        </div>
    </div>
@endsection
