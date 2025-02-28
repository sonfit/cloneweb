<div>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">
            Gửi
        </button>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>
