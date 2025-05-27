<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\InvoiceCreated;
use Illuminate\Support\Facades\Mail;
use App\Services\InvoiceService;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except(['clientSales', 'clientShowSale']);
    }

    /**
     * Display a listing of the sales.
     */
    public function index()
    {
        $sales = Sale::with(['client', 'admin'])->latest()->paginate(10);
        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        $clients = Client::with('user')->get()->pluck('company_name', 'id');
        $statuses = Status::pluck('name', 'name');
        return view('sales.create', compact('clients', 'statuses'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|exists:statuses,name',
            'markup_percentage' => 'nullable|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $sale = Sale::create([
            'client_id' => $validated['client_id'],
            'admin_id' => Auth::id(),
            'status' => $validated['status'],
            'markup_percentage' => $validated['markup_percentage'] ?? 0,
        ]);

        // Process products
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $markup = 1 + ($validated['markup_percentage'] ?? 0) / 100;
            $unitPrice = $product->base_price * $markup;

            $sale->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'unit_price' => $unitPrice,
            ]);
        }

        return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['client.user', 'products']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale)
    {
        $clients = Client::with('user')->get()->pluck('company_name', 'id');
        $statuses = Status::pluck('name', 'name');
        $sale->load('products');
        return view('sales.edit', compact('sale', 'clients', 'statuses'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|exists:statuses,name',
            'markup_percentage' => 'nullable|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $sale->update([
            'client_id' => $validated['client_id'],
            'status' => $validated['status'],
            'markup_percentage' => $validated['markup_percentage'] ?? $sale->markup_percentage,
        ]);

        // Update products
        $sale->products()->detach();
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $markup = 1 + ($validated['markup_percentage'] ?? $sale->markup_percentage) / 100;
            $unitPrice = $product->base_price * $markup;

            $sale->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'unit_price' => $unitPrice,
            ]);
        }

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
    }

    /**
     * Generate an invoice for the sale.
     */
    public function generateInvoice(Sale $sale)
    {
        if (!$sale->invoice_number) {
            $sale->invoice_number = 'INV-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            $sale->invoice_date = Carbon::now();
            $sale->status = 'Invoice Sent';
            $sale->save();

            // Send email notification about the invoice
            Mail::to($sale->client->user->email)->send(new InvoiceCreated($sale));
        }

        return $this->generatePdf($sale);
    }

    /**
     * Display sales for the current client.
     */
    public function clientSales()
    {
        $client = Auth::user()->client;
        $sales = $client->sales()->latest()->paginate(10);
        return view('client.sales.index', compact('sales'));
    }

    /**
     * Display a specific sale for the current client.
     */
    public function clientShowSale(Sale $sale)
    {
        if ($sale->client_id !== Auth::user()->client->id) {
            abort(403);
        }

        $sale->load('products');
        return view('client.sales.show', compact('sale'));
    }

    private function generatePdf(Sale $sale)
    {
        $invoiceService = new InvoiceService($sale);
        $pdf = $invoiceService->generateInvoicePdf();
        return $pdf->Output('D', 'Invoice_' . $sale->invoice_number . '.pdf');
    }
}
