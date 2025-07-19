<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use Illuminate\Http\Request;

class DepartemenController
{
    public function show ()
    {
        $departemen = Departemen::all();

        return view('pages.admin.departemen', [
            'title'=> 'departemen',
            'departemen'=> $departemen
        ]);
    }

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

    // Tampilkan form edit
    public function edit($id)
    {
        $departemen = Departemen::findOrFail($id);
        return view('pages.admin.edit_departemen', compact('departemen'));
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

        return redirect()->route('departemen.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    //Hapus
    public function destroy($id)
    {
        $departemen = Departemen::findOrFail($id);
        $departemen->delete();

        return redirect()->back()->with('success', 'Departemen berhasil dihapus.');
    }

}
