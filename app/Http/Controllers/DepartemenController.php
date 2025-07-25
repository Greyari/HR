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
    // Tampilkan Halaman Awal
    // ========================
    public function show()
    {
        $departemen = Departemen::paginate(5);
        $departemen->setPath('/admin/departemen/search');

        return view('pages.admin.departemen', [
            'title' => 'departemen',
            'departemen' => $departemen
        ]);
    }

    // ========================
    // Tambah Data
    // ========================
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_departemen' => [
                    'required',
                    'string',
                    'max:255',
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

            $nama = ucwords(strtolower($request->nama_departemen));
            $request->merge(['nama_departemen' => $nama]);

            Departemen::create([
                'nama_departemen' => $request->nama_departemen
            ]);

            $perPage  = 5;
            $total    = Departemen::count();
            $lastPage = ceil($total / $perPage);

            $departemen = Departemen::orderBy('id', 'asc')
                ->paginate($perPage, ['*'], 'page', $lastPage);

            $departemen->setPath('/admin/departemen/search');

            $paginationHtml = $departemen
                ->links('components.admin.departemen.pagination-departemen')->render();

            $tableHtml = View::make('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();

            return response()->json([
                'status'     => 'success',
                'message'    => 'Departemen berhasil ditambahkan',
                'table'      => $tableHtml,
                'pagination' => $paginationHtml,
                'total'      => $total,
            ]);
        }

        catch (ValidationException $e) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Data tidak valid, mohon periksa kembali.',
                'errors'  => $e->errors()
            ], 422);
        }

        catch (\Exception $e) {
            Log::error('Error di store departemen: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan di sisi server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ========================
    // Update/Edit Data
    // ========================
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_departemen' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[^0-9]*$/',
                    Rule::unique('departemen', 'nama_departemen')->ignore($id),
                ]
            ], [
                'nama_departemen.required' => 'Nama departemen wajib diisi.',
                'nama_departemen.string'   => 'Nama departemen harus berupa teks.',
                'nama_departemen.max'      => 'Nama departemen maksimal 255 karakter.',
                'nama_departemen.unique'   => 'Nama departemen sudah tersedia.',
                'nama_departemen.regex'    => 'Nama departemen tidak boleh mengandung angka.'
            ]);

            $nama = ucwords(strtolower($request->nama_departemen));
            $request->merge(['nama_departemen' => $nama]);

            $departemen = Departemen::findOrFail($id);
            $departemen->update([
                'nama_departemen' => $request->nama_departemen,
            ]);

            $perPage = 5;
            $keyword = $request->input('q', '');
            $page    = $request->input('page', 1);

            $departemen = Departemen::query()
                ->when($keyword, fn($q) => $q->where('nama_departemen', 'like', "%$keyword%"))
                ->orderBy('id', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            $departemen->setPath('/admin/departemen/search');

            $paginationHtml = $departemen
                ->links('components.admin.departemen.pagination-departemen')->render();

            $tableHtml = View::make('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();

            return response()->json([
                'status'     => 'success',
                'message'    => 'Departemen berhasil diedit',
                'table'      => $tableHtml,
                'pagination' => $paginationHtml,
                'total'      => $departemen->total(),
            ]);
        }

        catch (ValidationException $e) {
            return response()->json([
                'status'  => 'validation_error',
                'message' => 'Data tidak valid, mohon periksa kembali.',
                'errors'  => $e->errors()
            ], 422);
        }

        catch (\Exception $e) {
            Log::error('Error di update departemen: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan di sisi server.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ========================
    // Hapus Data
    // ========================
    public function destroy($id)
    {
        $departemen = Departemen::findOrFail($id);
        $departemen->delete();

        return redirect()->back()->with('success', 'Departemen berhasil dihapus.');
    }

    // ========================
    // Cari / Search Data
    // ========================
    public function search(Request $request)
    {
        $perPage = 5;
        $keyword = $request->query('q', '');

        $departemen = Departemen::when($keyword, function ($query, $keyword) {
            return $query->where('nama_departemen', 'like', "%$keyword%");
        })->orderBy('id', 'asc')->paginate($perPage);

        $departemen->setPath('/admin/departemen/search');

        if ($request->ajax()) {
            $html = view('components.admin.departemen.body-tabel-departemen', compact('departemen'))->render();
            $pagination = $departemen
                ->appends(['q' => $keyword])
                ->links('components.admin.departemen.pagination-departemen')
                ->render();

            return response()->json([
                'html'       => $html,
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
