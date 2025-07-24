@forelse($departemen as $index => $item)
<tr class="hover:bg-gray-50 transition h-14">
    <td class="px-2 py-3 text-center">{{ $departemen->firstItem() + $index }}</td>
    <td class="px-4 py-3 whitespace-normal break-words">{{ $item->nama_departemen }}</td>

    <td class="px-4 py-3 text-center">
        @include('components.admin.departemen.tombol-aksi-departemen', ['item' => $item])
    </td>
</tr>
@empty
<tr>
    <td colspan="3" class="text-center py-4 text-gray-500">Data departemen tidak ada.</td>
</tr>
@endforelse
