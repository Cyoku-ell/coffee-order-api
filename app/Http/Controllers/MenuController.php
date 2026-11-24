<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    // Menampilkan semua menu
    public function index()
    {
        return response()->json([
            'message' => 'Daftar menu berhasil diambil.',
            'data' => Menu::latest()->get(),
        ]);
    }

    // Menampilkan detail satu menu
    public function show(Menu $menu)
    {
        return response()->json([
            'message' => 'Detail menu berhasil diambil.',
            'data' => $menu,
        ]);
    }

    // Menambahkan menu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('menus', 'public');
        }

        $menu = Menu::create($validated);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu,
        ], 201);
    }

    // Mengubah menu
    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }

            $validated['image'] = $request->file('image')
                ->store('menus', 'public');
        }

        $menu->update($validated);

        return response()->json([
            'message' => 'Menu berhasil diperbarui.',
            'data' => $menu,
        ]);
    }

    // Menghapus menu
    public function destroy(Menu $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus.',
        ]);
    }
}
