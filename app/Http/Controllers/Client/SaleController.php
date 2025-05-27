<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sale::query()
            ->where('client_id', Auth::user()->client->id)
            ->with(['admin']);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.sales.index', compact('sales'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        // Ensure the client can only view their own sales
        if ($sale->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized action.');
        }

        $sale->load(['admin', 'products']);
        return view('client.sales.show', compact('sale'));
    }
}
