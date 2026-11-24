<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Menu;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Menampilkan keranjang user
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $cart->load('cartItems.menu');

        $total = $cart->cartItems->sum(function ($item) {
            return $item->menu->price * $item->quantity;
        });

        return response()->json([
            'message' => 'Keranjang berhasil diambil.',
            'data' => $cart,
            'total_price' => $total,
        ]);
    }

    // Menambahkan menu ke keranjang
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);

        if (!$menu->is_available) {
            return response()->json([
                'message' => 'Menu sedang tidak tersedia.',
            ], 422);
        }

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $cartItem = $cart->cartItems()->firstOrNew([
            'menu_id' => $menu->id,
        ]);

        $cartItem->quantity += $validated['quantity'];
        $cartItem->save();

        $cartItem->load('menu');

        return response()->json([
            'message' => 'Menu berhasil ditambahkan ke keranjang.',
            'data' => $cartItem,
        ], 201);
    }

    // Mengubah quantity item
    public function update(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        // Memastikan item benar-benar milik user yang login
        $item = $cart->cartItems()->findOrFail($cartItem->id);

        $item->update([
            'quantity' => $validated['quantity'],
        ]);

        $item->load('menu');

        return response()->json([
            'message' => 'Jumlah menu berhasil diperbarui.',
            'data' => $item,
        ]);
    }

    // Menghapus item dari keranjang
    public function destroy(Request $request, CartItem $cartItem)
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        // Memastikan item benar-benar milik user yang login
        $item = $cart->cartItems()->findOrFail($cartItem->id);

        $item->delete();

        return response()->json([
            'message' => 'Item berhasil dihapus dari keranjang.',
        ]);
    }
}
