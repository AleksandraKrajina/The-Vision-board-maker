<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisionBoardController extends Controller
{
    public function presets(): JsonResponse
    {
        return response()->json([
            'sizes' => [
                ['key' => 'laptop', 'value' => '1920x1080'],
                ['key' => 'tablet', 'value' => '2048x1536'],
                ['key' => 'phone', 'value' => '1170x2532'],
                ['key' => 'a1', 'value' => '7016x9933'],
                ['key' => 'a2', 'value' => '4961x7016'],
            ],
            'categories' => ['travel', 'work', 'love', 'star'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string'],
            'images' => ['array'],
            'images.*.src' => ['required', 'string'],
            'images.*.category' => ['required', 'string'],
        ]);

        return response()->json([
            'message' => 'Vision board payload accepted.',
            'board' => $validated,
        ], 201);
    }
}
