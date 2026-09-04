<?php

namespace App\Filament\Resources\KodeSpms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KodeSpmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Detail Kode SPM')
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode SPM')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),
                        TextInput::make('nama')
                            ->label('Nama SPM')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Select::make('jalur')
                            ->label('Jalur Verifikasi')
                            ->options([
                                '1_pihak' => '1 Pihak',
                                'banyak_pihak' => 'Banyak Pihak',
                                'gup' => 'GUP',
                            ])
                            ->required()
                            ->default('1_pihak'),
                    ])->columns(1)
            ]);
    }
}
