<?php

use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\GaleriController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index']);
Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');
