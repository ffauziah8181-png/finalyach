<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List kategori global + kategori custom milik user, opsional filter tipe.
     */
    public function index(Request $request)
    {
        $query = Category::where(function ($q) use ($request) {
            $q->whereNull('user_id')->orWhere('user_id', $request->user()->id);
        });

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        return response()->json($query->orderBy('nama')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', 'in:pengeluaran,pemasukan'],
            'icon' => ['nullable', 'string', 'max:50'],
            'warna' => ['nullable', 'string', 'max:20'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['is_default'] = false;

        $category = Category::create($data);

        return response()->json(['message' => 'Kategori berhasil ditambahkan.', 'data' => $category], 201);
    }

    public function destroy(Request $request, Category $category)
    {
        abort_if($category->user_id !== $request->user()->id, 403, 'Tidak diizinkan menghapus kategori bawaan.');
        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
