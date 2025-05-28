<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;

class QuickSaleController extends Controller
{
    public function create()
    {
        $clients = Client::all();
        $products = Product::all();
        return view('admin.quick_sales.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'client_id' => 'required_without:new_client',
            'new_client_name' => 'required_if:new_client,on',
            'new_client_email' => 'nullable|email',
            'new_client_phone' => 'nullable',
            'new_client_address' => 'nullable',
            'sale_date' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required_without:products.*.new_product',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.new_product_name' => 'required_if:products.*.new_product,on',
            'products.*.new_product_description' => 'nullable',
            'products.*.new_product_price' => 'required_if:products.*.new_product,on|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create or select client
            if ($request->has('new_client') && $request->new_client === 'on') {
                $client = new Client();
                $client->name = $request->new_client_name;
                $client->email = $request->new_client_email;
                $client->phone = $request->new_client_phone;
                $client->address = $request->new_client_address;
                $client->save();
                $clientId = $client->id;
            } else {
                $clientId = $request->client_id;
            }

            // Create sale
            $sale = new Sale();
            $sale->client_id = $clientId;
            $sale->sale_date = $request->sale_date;
            $sale->total = 0; // Will calculate below
            $sale->save();

            // Process products
            $total = 0;
            foreach ($request->products as $productData) {
                // Create or select product
                if (isset($productData['new_product']) && $productData['new_product'] === 'on') {
                    $product = new Product();
                    $product->name = $productData['new_product_name'];
                    $product->description = $productData['new_product_description'] ?? '';
                    $product->price = $productData['new_product_price'];
                    $product->save();
                    $productId = $product->id;
                    $price = $product->price;
                } else {
                    $productId = $productData['id'];
                    $price = $productData['price'];
                }

                // Create sale detail
                $saleDetail = new SaleDetail();
                $saleDetail->sale_id = $sale->id;
                $saleDetail->product_id = $productId;
                $saleDetail->quantity = $productData['quantity'];
                $saleDetail->price = $price;
                $saleDetail->subtotal = $price * $productData['quantity'];
                $saleDetail->save();

                $total += $saleDetail->subtotal;
            }

            // Update sale total
            $sale->total = $total;
            $sale->save();

            DB::commit();
            return redirect()->route('admin.sales.show', $sale->id)->with('success', 'Quick sale created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
}
