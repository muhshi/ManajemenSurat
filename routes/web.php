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

// ─── DEV MODE BYPASS LOGIN ───────────────────────────────────────
if (app()->environment('local')) {
    Route::get('/dev/login', function () {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@bps.go.id'],
            ['name' => 'Admin Dev', 'password' => bcrypt('password')]
        );
        
        // Coba assign role super admin jika ada Filament Shield (tanpa Artisan command)
        try {
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
                if (!$user->hasRole('super_admin')) {
                    $user->assignRole($role);
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika tidak ada Shield
        }

        auth()->login($user);
        return redirect('/admin');
    });
}

// ─── FILE DOWNLOAD BYPASS ─────────────────────────────────────────
Route::get('/download-export/{filename}', function ($filename) {
    // Pastikan nama file aman
    $filename = basename($filename);
    $path = storage_path('app/public/exports/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'File not found or already deleted.');
    }
    
    return response()->download($path)->deleteFileAfterSend();
})->name('download.export')->middleware('auth');
