<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OwnerOrderController extends Controller
{
    // Menampilkan semua pesanan pelanggan
    public function index()
    {
        $orders = Order::with(['user', 'orderItems'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Semua pesanan berhasil diambil.',
            'data' => $orders,
        ]);
    }

    // Melihat detail pesanan
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems']);

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $order,
        ]);
    }

    // Mengubah status pesanan
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,processing,completed,cancelled',
            ],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => $order,
        ]);
    }
}
