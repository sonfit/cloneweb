<div class="flex flex-wrap justify-center items-center gap-4 w-full h-full p-4">
    @foreach($urls as $fileUrl)
        @php
            $ext = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4','webm','ogg','mov','avi']);
        @endphp

        <div class="max-w-xs max-h-[50vh] flex justify-center items-center">
            @if($isVideo)
                <video controls class="max-w-full max-h-[50vh] rounded shadow-lg">
                    <source src="{{ $fileUrl }}" type="video/{{ $ext }}">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            @else
                <img src="{{ $fileUrl }}"
                     class="max-w-full max-h-[50vh] object-contain rounded shadow-lg"
                     alt="media">
            @endif
        </div>
    @endforeach
</div>
