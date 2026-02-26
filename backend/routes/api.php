<?php

use App\Http\Controllers\VisionBoardController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::get('/presets', [VisionBoardController::class, 'presets']);
Route::post('/boards', [VisionBoardController::class, 'store']);
