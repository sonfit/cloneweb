<div>
    <!-- Hiển thị tổng số đơn vị lên đầu -->
    <div class="mb-4 font-bold">
        Tổng số đơn vị: {{ $data['users']->count() }}
    </div>

    <!-- Hiển thị khoảng thời gian đã lọc -->
    <div class="mb-4">
        <strong>Từ ngày:</strong> {{ $data['from']->format('d/m/Y') }}
        <strong>Đến ngày:</strong> {{ $data['to']->format('d/m/Y') }}
    </div>
    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 px-4 py-2">Tên</th>
            <th class="border border-gray-300 px-4 py-2">Tên Đơn Vị </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($data['users'] as $user)
            <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $user->name }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $user->name_full }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
