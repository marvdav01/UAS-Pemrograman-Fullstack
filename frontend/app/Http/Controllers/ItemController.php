<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        $items      = Item::with('category')->latest()->get();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Items/Index', [
            'items'      => $items,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:items,code',
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
        ], [
            'code.unique'         => 'Kode peralatan sudah digunakan.',
            'category_id.exists'  => 'Kategori tidak ditemukan.',
        ]);

        Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function destroy(int $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Peralatan berhasil dihapus.');
    }
}
