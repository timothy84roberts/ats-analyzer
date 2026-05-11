@extends('layout.main')
@section('title', 'Contact Page')
@section('content')
    <div class="home">
        <h1>Contact Page</h1>
        <div class="contact-form">
            <form action="{{ route('contact.send') }}" method="post">
                @csrf
                <div class="form-inner">
                    <div class="row">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}">
                        @error('name')
                        <span>{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="name" value="{{ old('email') }}">
                        @error('email')
                        <span>{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row">
                        <label for="content">Content</label>
                        <textarea name="content" id="content" cols="30" rows="10"></textarea>
                        @error('content')
                        <span>{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="btn">
                        <button type="submit">Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
