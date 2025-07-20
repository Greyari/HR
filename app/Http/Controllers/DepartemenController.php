<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;

class DepartemenController
{
    // Menampilkan halaman
    public function show ()
    {
        $departemen = Departemen::all();

        return view('pages.admin.departemen', [
            'title'=> 'departemen',
            'departemen'=> $departemen
        ]);
    }

    // List tabel departemen
    public function store(Request $request)
    {
        $request->validate([
            'nama_departemen' => 'required|string|max:255|unique:departemen,nama_departemen'
        ]);

        Departemen::create([
            'nama_departemen' => $request->nama_departemen
        ]);

        return redirect()->back()->with('success', 'Departemen berhasil ditambahkan.');
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
        $keyword = $request->input('q');

        $departemen = Departemen::where('nama_departemen', 'like', "%{$keyword}%")->get();

        return response()->json([
            'html' => view('components.admin.departemen.tabel-data-hasil-cari-departemen', compact('departemen'))->render()
        ]);

    }
}
