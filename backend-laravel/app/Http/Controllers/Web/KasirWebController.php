<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasirWebController extends Controller
{
    public function pos()
    {
        $products = Product::where('status', 'aktif')->get();
        return view('kasir.pos', compact('products'));
    }

    public function shift()
    {
        $shift = Shift::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->latest()->first();
        return view('kasir.shift', compact('shift'));
    }

    public function transactions()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()->paginate(15);
        return view('kasir.transactions', compact('transactions'));
    }
}