<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['client', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('company_name')->get();
        $statuses = Status::orderBy('name')->get();

        return view('admin.sales.create', compact('clients', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|string',
            'markup_percentage' => 'nullable|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Create sale
        $sale = new Sale([
            'client_id' => $validated['client_id'],
            'admin_id' => Auth::id(),
            'status' => $validated['status'],
            'markup_percentage' => $validated['markup_percentage'],
        ]);
        $sale->save();

        // Attach products to the sale
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $unitPrice = $product->base_price;

            // Apply markup if provided
            if ($sale->markup_percentage) {
                $unitPrice *= (1 + ($sale->markup_percentage / 100));
            }

            $sale->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'unit_price' => $unitPrice
            ]);
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['client', 'admin', 'products']);
        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        $clients = Client::orderBy('company_name')->get();
        $statuses = Status::orderBy('name')->get();
        $sale->load('products');

        return view('admin.sales.edit', compact('sale', 'clients', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|string',
            'markup_percentage' => 'nullable|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Update sale
        $sale->update([
            'client_id' => $validated['client_id'],
            'status' => $validated['status'],
            'markup_percentage' => $validated['markup_percentage'],
        ]);

        // Detach all existing products
        $sale->products()->detach();

        // Attach products to the sale with new quantities and prices
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $unitPrice = $product->base_price;

            // Apply markup if provided
            if ($sale->markup_percentage) {
                $unitPrice *= (1 + ($sale->markup_percentage / 100));
            }

            $sale->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'unit_price' => $unitPrice
            ]);
        }

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    /**
     * Generate invoice PDF.
     */
    public function generateInvoice(Sale $sale)
    {
        $sale->load(['client', 'admin', 'products']);

        // Check if the sale is in a state that should have an invoice number
        if ($sale->status === 'Quote Generated' || $sale->status === 'Invoice Sent' ||
            $sale->status === 'Payment Pending' || $sale->status === 'Payment Received' ||
            $sale->status === 'Completed') {

            // Generate an invoice number if it doesn't exist yet
            if (!$sale->invoice_number) {
                $sale->invoice_number = 'INV-' . date('Y') . '-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT);
                $sale->invoice_date = now();
                $sale->save();
            }
        }

        $data = [
            'sale' => $sale,
            'total' => $sale->calculateTotal(),
        ];

        // Check if you have installed a PDF package like barryvdh/laravel-dompdf
        // Uncomment this when you have one:
        // $pdf = PDF::loadView('admin.sales.invoice', $data);
        // return $pdf->download('invoice-' . $sale->invoice_number . '.pdf');

        // For now, just return a view
        return view('admin.sales.invoice', $data);
    }
}
