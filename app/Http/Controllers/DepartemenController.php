<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
           $request->validate(
                [
                    'nama_departemen' => [
                        'required',
                        'string',
                        'max:255',
                        'unique:departemen,nama_departemen',
                        'regex:/^[^0-9]*$/'
                    ]
                ],
                [
                    'nama_departemen.required' => 'Nama departemen wajib diisi.',
                    'nama_departemen.string' => 'Nama departemen harus berupa teks.',
                    'nama_departemen.max' => 'Nama departemen maksimal 255 karakter.',
                    'nama_departemen.unique' => 'Nama departemen telah tersedia.',
                    'nama_departemen.regex' => 'Nama departemen tidak boleh mengandung angka.'
                ]
            );

            $nama = ucwords(strtolower($request->nama_departemen));
            $request->merge(['nama_departemen' => $nama]);

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
                'status' => 'success',
                'message' => 'Departemen Berhasil di tambahkan ini di kontroler',
                'table' => $tableHtml,
                'pagination' => $paginationHtml,
                'total' => $total,
            ]);
        }

        catch (ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Data tidak valid, mohon periksa kembali.',
                'errors' => $e->errors()
            ], 422);
        }

        catch (\Exception $e) {
            Log::error('Error di store departemen: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan di sisi server.',
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
