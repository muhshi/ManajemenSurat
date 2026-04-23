<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/inventory-uploads/{record}/print', function (\App\Models\InventoryUpload $record) {
    $transactions = $record->transactions()->with('item')
        ->orderBy('tanggal')
        ->orderBy('no_dok')
        ->get();
    
    // Group by Date and Document Number
    $grouped = $transactions->groupBy(function ($tx) {
        return $tx->tanggal . '|' . $tx->no_dok;
    });
    
    return view('reports.inventory-print', compact('record', 'grouped'));
})->name('inventory-upload.print');

// ─── SSO SIPETRA ────────────────────────────────────────────────
use App\Http\Controllers\Auth\SsoController;

Route::get('/auth/sipetra/redirect',  [SsoController::class, 'redirect'])->name('sipetra.login');
Route::get('/auth/sipetra/callback', [SsoController::class, 'callback'])->name('sipetra.callback');

