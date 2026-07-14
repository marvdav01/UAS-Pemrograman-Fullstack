<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $apiBase = config('services.api.base_url');
        $token   = Session::get('api_token', '');

        $itemsResp  = Http::withToken($token)->get("{$apiBase}/items");
        $catsResp   = Http::withToken($token)->get("{$apiBase}/categories");
        $trxResp    = Http::withToken($token)->get("{$apiBase}/transactions");

        $items        = $itemsResp->json('data') ?? $itemsResp->json() ?? [];
        $categories   = $catsResp->json('data')  ?? $catsResp->json()  ?? [];
        $transactions = $trxResp->json('data')   ?? $trxResp->json()   ?? [];

        $checkinCount = count(array_filter($transactions, fn($t) => ($t['status'] ?? '') === 'checkin'));
        $recentTrx    = array_slice(array_reverse($transactions), 0, 5);

        return Inertia::render('Dashboard', [
            'stats' => [
                'items'        => count($items),
                'categories'   => count($categories),
                'transactions' => count($transactions),
                'checkin'      => $checkinCount,
            ],
            'recentTransactions' => $recentTrx,
        ]);
    }
}
