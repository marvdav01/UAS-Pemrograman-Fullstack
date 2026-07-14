<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('item.category')
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'              => $t->id,
                'borrower_name'   => $t->borrower_name,
                'borrower_nim'    => $t->borrower_nim,
                'borrow_date'     => $t->borrow_date,
                'return_date'     => $t->return_date,
                'status'          => $t->status,
                'notes'           => $t->notes,
                'checkout_notes'  => $t->checkout_notes,
                'item_name'       => $t->item->name ?? '-',
                'item_code'       => $t->item->code ?? '-',
                'created_at'      => $t->created_at,
            ]);

        $items = Item::where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'stock']);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'items'        => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_nim'  => 'required|string|max:50',
            'item_id'       => 'required|exists:items,id',
            'borrow_date'   => 'required|date',
            'return_date'   => 'required|date|after_or_equal:borrow_date',
            'notes'         => 'nullable|string',
            'status'        => 'nullable|string|in:checkin,pending',
        ], [
            'item_id.exists'             => 'Peralatan tidak ditemukan.',
            'return_date.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        // Validasi stok tersedia
        $item = Item::findOrFail($validated['item_id']);
        if ($item->stock <= 0) {
            return back()->withErrors(['item_id' => 'Stok peralatan sudah habis.']);
        }

        DB::transaction(function () use ($validated, $item) {
            Transaction::create(array_merge($validated, ['status' => $validated['status'] ?? 'checkin']));
            // Kurangi stok
            $item->decrement('stock');
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi peminjaman berhasil dibuat.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'status'         => 'required|string|in:checkin,checkout,pending',
            'checkout_notes' => 'nullable|string',
        ]);

        $transaction = Transaction::findOrFail($id);

        DB::transaction(function () use ($transaction, $validated) {
            // Jika status berubah ke checkout, kembalikan stok
            if ($validated['status'] === 'checkout' && $transaction->status !== 'checkout') {
                $transaction->item->increment('stock');
            }
            $transaction->update($validated);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Status transaksi berhasil diperbarui.');
    }
}
