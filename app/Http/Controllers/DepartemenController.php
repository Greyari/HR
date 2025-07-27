<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DepartemenController
{
    // ========================
    // Tampilkan Halaman Utama
    // ========================
    public function show()
    {
        $departemen = Departemen::paginate(5);
        $departemen->setPath('/admin/departemen/search');

        return view('pages.admin.departemen', [
            'title'      => 'departemen',
            'departemen' => $departemen
        ]);
    }

    // ========================
    // Tambah Data Departemen
    // ========================
    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'nama_departemen' => [
                    'required', 'string', 'max:255',
                    'unique:departemen,nama_departemen',
                    'regex:/^[^0-9]*$/'
                ]
            ], [
                'nama_departemen.required' => 'Nama departemen wajib diisi.',
                'nama_departemen.string'   => 'Nama departemen harus berupa teks.',
                'nama_departemen.max'      => 'Nama departemen maksimal 255 karakter.',
                'nama_departemen.unique'   => 'Nama departemen telah tersedia.',
                'nama_departemen.regex'    => 'Nama departemen tidak boleh mengandung angka.'
            ]);

            $nama = $request->nama_departemen;

            // Simpan ke database
            Departemen::create([ 'nama_departemen' => $nama ]);

            // Ambil data halaman terakhir
            $perPage = 5;
            $total   = Departemen::count();
            $page    = ceil($total / $perPage);

            $departemen = Departemen::orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);
            $departemen->setPath('/admin/departemen/search');

            // Render HTML untuk dikirim ke frontend
            $paginationHtml = $departemen->links('components.admin.departemen.pagination-departemen')->render();
            $tabelHtml      = View::make('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();

            return response()->json([
                'status'     => 'success',
                'message'    => 'Departemen berhasil ditambahkan',
                'tabel'      => $tabelHtml,
                'pagination' => $paginationHtml,
                'total'      => $total,
                'page_valid' => $page
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Data tidak valid, mohon periksa kembali.',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error di store departemen: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan di sisi server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ========================
    // Update Data Departemen
    // ========================
    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $request->validate([
                'nama_departemen' => [
                    'required', 'string', 'max:255', 'regex:/^[^0-9]*$/',
                    Rule::unique('departemen', 'nama_departemen')->ignore($id),
                ]
            ], [
                'nama_departemen.required' => 'Nama departemen wajib diisi.',
                'nama_departemen.string'   => 'Nama departemen harus berupa teks.',
                'nama_departemen.max'      => 'Nama departemen maksimal 255 karakter.',
                'nama_departemen.unique'   => 'Nama departemen sudah tersedia.',
                'nama_departemen.regex'    => 'Nama departemen tidak boleh mengandung angka.',
            ]);
            $namaBaru = $request->nama_departemen;

            // Ambil data lama dari DB
            $departemen = Departemen::findOrFail($id);

            // Validasi: nama baru tidak boleh sama persis dengan sebelumnya
            if ($departemen->nama_departemen === $namaBaru) {
                return response()->json([
                    'status' => 'validation_error',
                    'message' => 'Data tidak valid, mohon periksa kembali.',
                    'errors' => [
                        'nama_departemen' => ['Data tidak boleh sama dengan sebelumnya.']
                    ]
                ], 422);
            }

            // Update data
            $departemen->update(['nama_departemen' => $namaBaru]);

            // Ambil data sesuai page dan keyword
            $perPage = 5;
            $keyword = $request->input('q', '');
            $page    = $request->input('page', 1);

            $departemen = Departemen::query()
                ->when($keyword, fn($q) => $q->where('nama_departemen', 'like', "%$keyword%"))
                ->orderBy('id', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            $departemen->setPath('/admin/departemen/search');

            // Render hasil
            $paginationHtml = $departemen->links('components.admin.departemen.pagination-departemen')->render();
            $tabelHtml      = View::make('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();

            return response()->json([
                'status'     => 'success',
                'message'    => 'Departemen berhasil diedit',
                'tabel'      => $tabelHtml,
                'pagination' => $paginationHtml,
                'total'      => $departemen->total(),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Data tidak valid, mohon periksa kembali.',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error di update departemen: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan di sisi server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ========================
    // Hapus Data Departemen
    // ========================
    public function destroy(Request $request, $id)
    {
        try {
            $departemen = Departemen::findOrFail($id);
            $departemen->delete();

            // Ambil ulang data
            $perPage        = 5;
            $keyword        = $request->input('q', '');
            $page           = (int) $request->input('page', 1);
            $recentlyAdded  = $request->boolean('recently_added', false);

            $query = Departemen::query()
                ->when($keyword, fn($q) => $q->where('nama_departemen', 'like', "%$keyword%"))
                ->orderBy('id', 'asc');

            $total    = $query->count();
            $lastPage = max(ceil($total / $perPage), 1);
            $page     = $recentlyAdded ? $lastPage : min($page, $lastPage);

            $departemen = $query->paginate($perPage, ['*'], 'page', $page);
            $departemen->setPath('/admin/departemen/search');

            $tabelHtml      = View::make('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();
            $paginationHtml = $departemen->links('components.admin.departemen.pagination-departemen')->render();

            return response()->json([
                'status'     => 'success',
                'message'    => 'Departemen berhasil dihapus.',
                'tabel'      => $tabelHtml,
                'pagination' => $paginationHtml,
                'total'      => $total,
                'page_valid' => $page,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Data tidak valid, mohon periksa kembali.',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error di hapus departemen: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan di sisi server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ========================
    // Pencarian Data AJAX
    // ========================
    public function search(Request $request)
    {
        $perPage = 5;
        $keyword = $request->query('q', '');

        $departemen = Departemen::when($keyword, fn($q) => $q->where('nama_departemen', 'like', "%$keyword%"))
            ->orderBy('id', 'asc')
            ->paginate($perPage);

        $departemen->setPath('/admin/departemen/search');

        if ($request->ajax()) {
            $tabel      = view('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();
            $pagination = $departemen->appends(['q' => $keyword])
                ->links('components.admin.departemen.pagination-departemen')
                ->render();

            return response()->json([
                'tabel'      => $tabel,
                'pagination' => $pagination,
                'total'      => $departemen->total(),
            ]);
        }

        return view('pages.admin.departemen', [
            'title'      => 'departemen',
            'departemen' => $departemen
        ]);
    }
}
