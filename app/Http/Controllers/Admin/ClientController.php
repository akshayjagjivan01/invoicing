<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::with('user')->orderBy('company_name')->paginate(10);
        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'shipping_address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'client',
        ]);

        // Create client profile
        $client = new Client([
            'company_name' => $validated['company_name'],
            'billing_address' => $validated['billing_address'],
            'shipping_address' => $validated['shipping_address'],
            'contact_person' => $validated['contact_person'],
            'phone_number' => $validated['phone_number'],
        ]);

        $user->client()->save($client);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $client->load('user', 'sales');
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $client->load('user');
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $client->user_id,
            'company_name' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'shipping_address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ]);

        // Update user
        $user = $client->user;
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Update client
        $client->update([
            'company_name' => $validated['company_name'],
            'billing_address' => $validated['billing_address'],
            'shipping_address' => $validated['shipping_address'],
            'contact_person' => $validated['contact_person'],
            'phone_number' => $validated['phone_number'],
        ]);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // Get the user associated with this client
        $user = $client->user;

        // Delete the client (will cascade delete all sales)
        $client->delete();

        // Delete the user
        $user->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
