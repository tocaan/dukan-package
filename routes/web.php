<?php

use Illuminate\Support\Facades\Route;
use Tocaan\Dukan\Http\Controllers\DukanController;

Route::get('dukan', [DukanController::class, 'index']);
