<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email']),
            ImportColumn::make('nip'),
            ImportColumn::make('jabatan'),
            ImportColumn::make('golongan'),
            ImportColumn::make('nomor_hp'),
        ];
    }

    public function resolveRecord(): ?User
    {
        $user = User::firstOrNew([
            'email' => $this->data['email'],
        ]);

        if (!$user->exists) {
            $user->password = bcrypt('bps3321');
            $user->save();
            $user->assignRole('Pegawai');
        }

        return $user;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
