<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ItemController extends Controller
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
        $itemsResp = Http::withToken($this->token())->get("{$this->apiBase()}/items");
        $catsResp  = Http::withToken($this->token())->get("{$this->apiBase()}/categories");

        return Inertia::render('Items/Index', [
            'items'      => $itemsResp->json('data') ?? $itemsResp->json() ?? [],
            'categories' => $catsResp->json('data') ?? $catsResp->json() ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50',
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $response = Http::withToken($this->token())
            ->post("{$this->apiBase()}/items", $validated);

        if ($response->failed()) {
            return back()->withErrors($response->json('errors') ?? ['name' => 'Gagal menyimpan data.']);
        }

        return redirect('/items')->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function destroy(int $id)
    {
        $response = Http::withToken($this->token())
            ->delete("{$this->apiBase()}/items/{$id}");

        if ($response->failed()) {
            return back()->withErrors(['error' => 'Gagal menghapus data.']);
        }

        return redirect('/items')->with('success', 'Peralatan berhasil dihapus.');
    }
}
