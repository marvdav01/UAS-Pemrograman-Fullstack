<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class TransactionController extends Controller
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
        $trxResp   = Http::withToken($this->token())->get("{$this->apiBase()}/transactions");
        $itemsResp = Http::withToken($this->token())->get("{$this->apiBase()}/items");

        return Inertia::render('Transactions/Index', [
            'transactions' => $trxResp->json('data')   ?? $trxResp->json()   ?? [],
            'items'        => $itemsResp->json('data') ?? $itemsResp->json() ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_nim'  => 'required|string|max:50',
            'item_id'       => 'required',
            'borrow_date'   => 'required|date',
            'return_date'   => 'required|date|after_or_equal:borrow_date',
            'notes'         => 'nullable|string',
            'status'        => 'nullable|string|in:checkin,pending',
        ]);

        // Extra check: ensure item has stock
        $itemResp = Http::withToken($this->token())
            ->get("{$this->apiBase()}/items/{$validated['item_id']}");

        if ($itemResp->ok()) {
            $item = $itemResp->json('data') ?? $itemResp->json();
            if (isset($item['stock']) && $item['stock'] <= 0) {
                return back()->withErrors(['item_id' => 'Stok peralatan sudah habis.']);
            }
        }

        $response = Http::withToken($this->token())
            ->post("{$this->apiBase()}/transactions", $validated);

        if ($response->failed()) {
            return back()->withErrors($response->json('errors') ?? ['borrower_name' => 'Gagal menyimpan transaksi.']);
        }

        return redirect('/transactions')->with('success', 'Transaksi berhasil dibuat.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'status'          => 'required|string|in:checkin,checkout,pending',
            'checkout_notes'  => 'nullable|string',
        ]);

        $response = Http::withToken($this->token())
            ->put("{$this->apiBase()}/transactions/{$id}", $validated);

        if ($response->failed()) {
            return back()->withErrors(['error' => 'Gagal memperbarui transaksi.']);
        }

        return redirect('/transactions')->with('success', 'Transaksi berhasil diperbarui.');
    }
}
