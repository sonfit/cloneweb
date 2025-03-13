<div class="p-6">
    <!-- Header thông tin -->
    <div class="mb-4 font-bold text-xl text-center">
        Tổng số đơn vị: {{ $data['users']->count() }}
    </div>
    <div class="mb-4 text-center">
        <strong>Từ ngày:</strong> {{ $data['from']->format('d/m/Y') }} |
        <strong>Đến ngày:</strong> {{ $data['to']->format('d/m/Y') }}
    </div>

    <!-- Grid container -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-[calc(100vh-10rem)]">
        @foreach ($data['users']->chunk(ceil($data['users']->count() / 2)) as $chunkIndex => $usersChunk)
            <div class="flex flex-col h-full"> <!-- 2. Flex column container -->
                <div class="border rounded-lg shadow-md flex-1 flex flex-col"> <!-- 3. Flex grow -->
                    <div class="flex-1 overflow-auto"> <!-- 4. Scrollable area -->
                        <table class="w-full border-collapse border border-gray-300">
                            <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 w-12">STT</th>
                                <th class="border border-gray-300 px-4 py-2">Tên</th>
                                <th class="border border-gray-300 px-4 py-2">Tên Đơn Vị</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($usersChunk as $index => $user)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-center">
                                        {{ ($chunkIndex * $usersChunk->count()) + $loop->iteration }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $user->name }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $user->name_full }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
