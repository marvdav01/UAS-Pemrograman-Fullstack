<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Transaction;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems        = Item::count();
        $totalCategories   = Category::count();
        $totalTransactions = Transaction::count();
        $checkinCount      = Transaction::where('status', 'checkin')->count();

        $recentTransactions = Transaction::with('item')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'borrower_name'  => $t->borrower_name,
                'borrower_nim'   => $t->borrower_nim,
                'borrow_date'    => $t->borrow_date,
                'return_date'    => $t->return_date,
                'status'         => $t->status,
                'item_name'      => $t->item->name ?? '-',
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'items'        => $totalItems,
                'categories'   => $totalCategories,
                'transactions' => $totalTransactions,
                'checkin'      => $checkinCount,
            ],
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
