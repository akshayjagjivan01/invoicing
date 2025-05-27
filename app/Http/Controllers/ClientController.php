<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the clients.
     */
    public function index()
    {
        $clients = Client::with('user')->latest()->paginate(10);
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'shipping_address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($validated, &$client) {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'client',
            ]);

            // Create client
            $client = Client::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'billing_address' => $validated['billing_address'],
                'shipping_address' => $validated['shipping_address'],
                'contact_person' => $validated['contact_person'],
                'phone_number' => $validated['phone_number'],
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        $client->load('user', 'sales');
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        $client->load('user');
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $client->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'shipping_address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($validated, $client) {
            // Update user
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $client->user->update($userData);

            // Update client
            $client->update([
                'company_name' => $validated['company_name'],
                'billing_address' => $validated['billing_address'],
                'shipping_address' => $validated['shipping_address'],
                'contact_person' => $validated['contact_person'],
                'phone_number' => $validated['phone_number'],
            ]);
        });

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        DB::transaction(function () use ($client) {
            // This will cascade delete the client due to foreign key constraint
            $client->user->delete();
        });

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
