<?php

use App\Http\Controllers\OAuth\PkceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Auth service is running';
});

Route::get('/auth/redirect', [PkceController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback', [PkceController::class, 'callback'])->name('auth.callback');
Route::post('/auth/refresh', [PkceController::class, 'refresh'])->name('auth.refresh');
// Backchannel (Gateway only, HMAC auth)
Route::post('/auth/ticket/redeem', [PkceController::class, 'redeem'])->name('auth.ticket.redeem');

require __DIR__.'/auth.php';
