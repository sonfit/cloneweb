<x-filament::modal.heading>
    Chi tiết đăng ký của: {{ $userName }}
</x-filament::modal.heading>

<div class="space-y-4">
    @foreach($details as $item)
        <div class="border rounded p-4">
            <p><b>Thời gian:</b> {{ $item->created_at->format('d/m/Y H:i') }}</p>
            <p><b>Ô tô mục 3:</b> {{ $item->oto_muc_3 }}</p>
            <p><b>Xe máy mục 4:</b> {{ $item->xe_may_muc_4 }}</p>
            <!-- Thêm các trường khác tùy ý -->
        </div>
    @endforeach
</div>
