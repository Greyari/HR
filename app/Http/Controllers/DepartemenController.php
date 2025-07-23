<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class DepartemenController
{
    // Menampilkan halaman
    public function show()
    {
        $departemen = Departemen::paginate(5);
        $departemen->setPath('/admin/departemen/search');

        return view('pages.admin.departemen', [
            'title' => 'departemen',
            'departemen' => $departemen
        ]);
    }

    // Tambah departemen
    public function store(Request $request)
    {
        try {
            // 1. Validasi input: nama_departemen harus diisi, berupa string, max 255 karakter, dan unik
            $request->validate([
                'nama_departemen' => 'required|string|max:255|unique:departemen,nama_departemen'
            ]);

            // 2. Simpan data baru ke tabel 'departemen'
            Departemen::create([
                'nama_departemen' => $request->nama_departemen
            ]);

            // 3. Tentukan jumlah data per halaman
            $perPage = 5;

            // 4. Hitung total data departemen setelah penambahan
            $total = Departemen::count();

            // 5. Hitung halaman terakhir (lastPage) agar setelah menambahkan data, user tetap di halaman terakhir
            $lastPage = ceil($total / $perPage);

            // 6. Ambil data departemen untuk ditampilkan, urut berdasarkan ID ASC, pada halaman terakhir
            $departemen = Departemen::orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $lastPage);

            // 7. Pastikan path pagination tetap mengarah ke route pencarian AJAX
            $departemen->setPath('/admin/departemen/search');

            // 8. Render ulang komponen pagination dan tabel berdasarkan data terbaru
            $paginationHtml = $departemen->links('components.admin.departemen.pagination-departemen')->render();
            $tableHtml = View::make('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();

            // 9. Kirim response JSON berisi HTML tabel baru, pagination, total data, dan halaman terakhir
            return response()->json([
                'table' => $tableHtml,
                'pagination' => $paginationHtml,
                'total' => $total,
                'last_page' => $lastPage // opsional kalau frontend ingin scroll ke halaman terakhir
            ]);
        } catch (\Exception $e) {
            // 10. Jika terjadi error, log errornya dan kirim response error JSON
            Log::error('Error di store departemen: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Proses update data
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_departemen' => 'required|string|max:255|unique:departemen,nama_departemen,' . $id,
        ]);

        $departemen = Departemen::findOrFail($id);
        $departemen->update([
            'nama_departemen' => $request->nama_departemen,
        ]);

        return redirect()->back()->with('success', 'Departemen berhasil diperbarui.');
    }

    // Hapus data
    public function destroy($id)
    {
        $departemen = Departemen::findOrFail($id);
        $departemen->delete();

        return redirect()->back()->with('success', 'Departemen berhasil dihapus.');
    }

    // Cari data
    public function search(Request $request)
    {
        $keyword = $request->query('q');

        $departemen = Departemen::when($keyword, function ($query, $keyword) {
            $query->where('nama_departemen', 'like', "%$keyword%");
        })->paginate(5);

        $departemen->setPath('/admin/departemen/search');

        $html = view('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();
        $pagination = $departemen
            ->appends(['q' => $keyword])
            ->links('components.admin.departemen.pagination-departemen')
            ->render();

        return response()->json([
            'html' => $html,
            'pagination' => $pagination,
            'total' => $departemen->total(),
        ]);
    }


}
