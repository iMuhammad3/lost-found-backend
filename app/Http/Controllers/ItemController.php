<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // GET /api/items - list all items (with search & filter)
    public function index(Request $request)
    {
        $query = Item::with('user')->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());;
    }

    // POST /api/items - create a new item
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|string',
            'location'    => 'required|string',
            'status'      => 'required|in:lost,found',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item = $request->user()->items()->create($validated);
        $item->load('user');

        return response()->json($item, 201);
    }

    // GET /api/items/{id} - get a single item
    public function show(Item $item)
    {
        $item->load('user');
        return response()->json($item);
    }

    // PUT /api/items/{id} - update an item
    public function update(Request $request, Item $item)
    {
        if ($item->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'           => 'sometimes|string|max:255',
            'description'     => 'sometimes|string',
            'category'        => 'sometimes|string',
            'location'        => 'sometimes|string',
            'status'          => 'sometimes|in:lost,found',
            'recovery_status' => 'sometimes|in:active,recovered',
        ]);

        $item->update($validated);
        $item->load('user');

        return response()->json($item);
    }

    // DELETE /api/items/{id} - delete an item
    public function destroy(Request $request, Item $item)
    {
        if ($item->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Item deleted']);
    }
}