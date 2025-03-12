<div>
    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 px-4 py-2">Tên</th>
            <th class="border border-gray-300 px-4 py-2">Tên Đơn Vị </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $user->name }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $user->name_full }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
