<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Melihat riwayat pesanan milik user
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('orderItems')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat pesanan berhasil diambil.',
            'data' => $orders,
        ]);
    }

    // Checkout
    public function store(Request $request)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $order = DB::transaction(function () use ($user, $request) {

            $cart = Cart::where('user_id', $user->id)
                ->first();

            if (!$cart) {
                return null;
            }

            $cartItems = $cart->cartItems()
                ->with('menu')
                ->get();

            if ($cartItems->isEmpty()) {
                return null;
            }

            // Memastikan semua menu masih tersedia
            foreach ($cartItems as $item) {
                if (!$item->menu || !$item->menu->is_available) {
                    abort(422, 'Ada menu yang sudah tidak tersedia.');
                }
            }

            // Menghitung total harga
            $total = $cartItems->sum(function ($item) {
                return $item->menu->price * $item->quantity;
            });

            // Membuat pesanan
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'total_price' => $total,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Menyimpan detail setiap menu yang dipesan
            foreach ($cartItems as $item) {
                $order->orderItems()->create([
                    'menu_id' => $item->menu_id,
                    'menu_name' => $item->menu->name,
                    'price' => $item->menu->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->menu->price * $item->quantity,
                ]);
            }

            // Mengosongkan keranjang setelah checkout
            $cart->cartItems()->delete();

            return $order->load('orderItems');
        });

        if (!$order) {
            return response()->json([
                'message' => 'Keranjang masih kosong.',
            ], 422);
        }

        return response()->json([
            'message' => 'Checkout berhasil.',
            'data' => $order,
        ], 201);
    }

    // Melihat detail pesanan milik user
    public function show(Request $request, Order $order)
    {
        // User hanya boleh melihat pesanan miliknya sendiri
        abort_unless(
            $order->user_id === $request->user()->id,
            403,
            'Kamu tidak memiliki akses ke pesanan ini.'
        );

        $order->load('orderItems');

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $order,
        ]);
    }
}
