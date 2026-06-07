<?php

namespace App\Http\Controllers;

use App\Models\DdcClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DdcClassController extends Controller
{
    public function index(Request $request)
{
    if (!auth()->check() || (int) auth()->user()->role_id !== 1) {
        abort(403, 'Anda tidak memiliki akses.');
    }

    $keyword = trim((string) (
        $request->input('keyword')
        ?? $request->input('search')
        ?? $request->input('q')
        ?? ''
    ));

    $ddcClasses = \App\Models\DdcClass::query()
        ->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%");

                if (\Illuminate\Support\Facades\Schema::hasColumn('ddc_classes', 'description')) {
                    $subQuery->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        })
        ->orderBy('code')
        ->paginate(10)
        ->withQueryString();

    return view('pustakawan.ddc.index', compact(
        'ddcClasses',
        'keyword'
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:ddc_classes,code',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'code.required' => 'Kode DDC wajib diisi.',
            'code.unique' => 'Kode DDC ini sudah digunakan.',
            'code.max' => 'Kode DDC maksimal 20 karakter.',

            'name.required' => 'Nama kelas DDC wajib diisi.',
            'name.max' => 'Nama kelas DDC maksimal 255 karakter.',

            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ], [
            'code' => 'Kode DDC',
            'name' => 'Nama kelas DDC',
            'description' => 'Deskripsi',
        ]);

        DdcClass::create([
            'code' => trim($validated['code']),
            'name' => trim($validated['name']),
            'description' => !empty($validated['description'])
                ? trim($validated['description'])
                : null,
        ]);

        return redirect()
            ->route('ddc.index')
            ->with('success_title', 'DDC berhasil ditambahkan')
            ->with('success_message', 'Kelas DDC "' . $validated['code'] . ' - ' . $validated['name'] . '" berhasil ditambahkan.')
            ->with('success_detail', 'DDC ini sekarang bisa dipilih pada data Buku Induk.');
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'code.required' => 'Kode DDC wajib diisi.',
            'code.unique' => 'Kode DDC ini sudah digunakan oleh klasifikasi lain.',
            'code.max' => 'Kode DDC maksimal 20 karakter.',

            'name.required' => 'Nama kelas DDC wajib diisi.',
            'name.max' => 'Nama kelas DDC maksimal 255 karakter.',

            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ], [
            'code' => 'Kode DDC',
            'name' => 'Nama kelas DDC',
            'description' => 'Deskripsi',
        ]);

        $ddc->update([
            'code' => trim($validated['code']),
            'name' => trim($validated['name']),
            'description' => !empty($validated['description'])
                ? trim($validated['description'])
                : null,
        ]);

        return redirect()
            ->route('ddc.index')
            ->with('success_title', 'DDC berhasil diperbarui')
            ->with('success_message', 'Kelas DDC "' . $ddc->code . ' - ' . $ddc->name . '" berhasil diperbarui.')
            ->with('success_detail', 'Perubahan DDC akan digunakan oleh Buku Induk yang memakai kelas ini.');
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
            ->with('success_message', 'Kelas DDC "' . $code . ' - ' . $name . '" berhasil dihapus.')
            ->with('success_detail', 'Kelas DDC tersebut tidak akan tampil lagi pada daftar klasifikasi.');
    }
}