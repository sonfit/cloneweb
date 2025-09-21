<div class="flex space-x-2">
    @foreach(collect($getState())->take(3) as $path)
        @php
            $url = Storage::disk('public')->url($path);
            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4','webm','ogg','mov','avi']);
        @endphp

        @if($isVideo)
            <video class="h-16 w-24 rounded object-cover" muted>
                <source src="{{ $url }}" type="video/{{ $ext }}">
            </video>
        @else
            <img src="{{ $url }}" class="h-16 w-24 rounded object-cover">
        @endif
    @endforeach
{{--    @if(count($getState()) > 3)--}}
{{--        <span class="text-gray-500 text-sm">+{{ count($getState()) - 3 }}</span>--}}
{{--    @endif--}}
</div>
