<div class="card flex gap-4 p-3 border rounded mb-4">
    @if ($imgUrl)
    <div class="img">
        <img src="{{ $imgUrl }}" alt="{{$title}}">
    </div>
    @endif
    <div class="card-info">
        <div class="card-title text-lg font-bold">{{ $title }}</div>
        <div class="description">
            {{ $content }}
        </div>
    </div>
</div>