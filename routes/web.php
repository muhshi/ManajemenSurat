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
    
    // Group by Date
    $grouped = $transactions->groupBy('tanggal');
    
    return view('reports.inventory-print', compact('record', 'grouped'));
})->name('inventory-upload.print');
