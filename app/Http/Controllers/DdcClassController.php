<?php

namespace App\Http\Controllers;

use App\Models\DdcClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DdcClassController extends Controller
{
    public function index()
    {
        $ddcClasses = DdcClass::withCount('books')
            ->orderBy('code')
            ->get();

        return view('pustakawan.ddc.index', compact('ddcClasses'));
    }

    public function edit(DdcClass $ddc)
    {
        return view('pustakawan.ddc.edit', [
            'ddcClass' => $ddc,
        ]);
    }

    public function update(Request $request, DdcClass $ddc)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('ddc_classes', 'code')->ignore($ddc->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'code.required' => 'Kode DDC wajib diisi.',
            'code.unique' => 'Kode DDC ini sudah digunakan oleh klasifikasi lain.',
            'name.required' => 'Nama klasifikasi wajib diisi.',
        ]);

        $ddc->update([
            'code' => trim($validated['code']),
            'name' => trim($validated['name']),
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
        ]);

        return redirect()
            ->route('ddc.index')
            ->with('success_title', 'DDC berhasil diperbarui')
            ->with('success_message', 'Kelas DDC "' . $ddc->code . ' - ' . $ddc->name . '" berhasil diperbarui.')
            ->with('success_detail', 'Jumlah buku pada DDC akan dihitung otomatis dari Buku Induk.');
    }

    public function destroy(DdcClass $ddc)
    {
        $ddc->loadCount('books');

        if ($ddc->books_count > 0) {
            return redirect()
                ->route('ddc.index')
                ->with('error_title', 'DDC tidak bisa dihapus')
                ->with('error_message', 'Kelas DDC "' . $ddc->code . ' - ' . $ddc->name . '" masih digunakan oleh ' . $ddc->books_count . ' buku induk.')
                ->with('error_detail', 'Pindahkan buku induk ke DDC lain terlebih dahulu sebelum menghapus klasifikasi ini.');
        }

        $code = $ddc->code;
        $name = $ddc->name;

        $ddc->delete();

        return redirect()
            ->route('ddc.index')
            ->with('success_title', 'DDC berhasil dihapus')
            ->with('success_message', 'Kelas DDC "' . $code . ' - ' . $name . '" berhasil dihapus.');
    }
}