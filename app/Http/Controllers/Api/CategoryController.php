<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(protected CategoryRepositoryInterface $categories) {}

    public function index(Request $request)
    {
        $categories = $this->categories->availableForUser($request->user()->id, $request->get('tipe'));

        return $this->success('Berhasil mengambil daftar kategori.', $categories);
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

        $category = $this->categories->create($data);

        return $this->created('Kategori berhasil ditambahkan.', $category);
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorize('delete', $category);

        $this->categories->delete($category);

        return $this->success('Kategori berhasil dihapus.');
    }
}
