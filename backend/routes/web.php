<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'application' => 'DOST FMS API',
    'status' => 'online',
]));
