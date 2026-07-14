<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CategoryController extends Controller
{
    private function apiBase(): string
    {
        return config('services.api.base_url');
    }

    private function token(): string
    {
        return Session::get('api_token', '');
    }

    public function index()
    {
        $response = Http::withToken($this->token())
            ->get("{$this->apiBase()}/categories");

        return Inertia::render('Categories/Index', [
            'categories' => $response->json('data') ?? $response->json() ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $response = Http::withToken($this->token())
            ->post("{$this->apiBase()}/categories", $validated);

        if ($response->failed()) {
            return back()->withErrors($response->json('errors') ?? ['name' => 'Gagal menyimpan kategori.']);
        }

        return redirect('/categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroy(int $id)
    {
        $response = Http::withToken($this->token())
            ->delete("{$this->apiBase()}/categories/{$id}");

        if ($response->failed()) {
            return back()->withErrors(['error' => 'Gagal menghapus kategori.']);
        }

        return redirect('/categories')->with('success', 'Kategori berhasil dihapus.');
    }
}
