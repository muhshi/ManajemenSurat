<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisposisiResource\Pages;
use App\Filament\Resources\DisposisiResource\RelationManagers;
use App\Models\Disposisi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DisposisiResource extends Resource
{
    protected static ?string $model = Disposisi::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Surat Masuk';
    protected static ?string $navigationLabel = 'Riwayat Disposisi';
    protected static ?int $navigationSort = 11;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'Kepala', 'Kasubag'])) {
            return $query;
        }

        return $query->where('penerima_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konteks Surat')
                    ->schema([
                        Forms\Components\Select::make('surat_masuk_id')
                            ->relationship('suratMasuk', 'nomor_surat')
                            ->disabled(),
                        Forms\Components\TextInput::make('sifat')
                            ->disabled(),
                    ])->columns(2),
                Forms\Components\Section::make('Detail Disposisi')
                    ->schema([
                        Forms\Components\Select::make('pengirim_id')
                            ->relationship('pengirim', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('penerima_id')
                            ->relationship('penerima', 'name')
                            ->disabled(),
                        Forms\Components\Textarea::make('catatan')
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Belum Dibaca' => 'Belum Dibaca',
                                'Dilihat' => 'Dilihat',
                                'Selesai' => 'Selesai',
                            ])->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('suratMasuk.nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('suratMasuk.perihal')
                    ->label('Perihal')
                    ->limit(30)
                    ->tooltip(fn($state) => $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('pengirim.name')
                    ->label('Pemberi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('penerima.name')
                    ->label('Penerima Staff')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Belum Dibaca' => 'danger',
                        'Dilihat' => 'warning',
                        'Selesai' => 'success',
                        default => 'gray',
                    })
                    ->action(
                        Tables\Actions\Action::make('updateStatus')
                            ->form([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'Belum Dibaca' => 'Belum Dibaca',
                                        'Dilihat' => 'Dilihat',
                                        'Selesai' => 'Selesai',
                                    ])
                                    ->required(),
                            ])
                            ->action(function (Disposisi $record, array $data): void {
                                $record->update($data);
                            })
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Disposisi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sifat')
                    ->options([
                        'Biasa' => 'Biasa',
                        'Segera' => 'Segera',
                        'Penting' => 'Penting',
                        'Rahasia' => 'Rahasia',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Belum Dibaca' => 'Belum Dibaca',
                        'Dilihat' => 'Dilihat',
                        'Selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisposisis::route('/'),
            'create' => Pages\CreateDisposisi::route('/create'),
            'view' => Pages\ViewDisposisi::route('/{record}'),
            'edit' => Pages\EditDisposisi::route('/{record}/edit'),
        ];
    }
}
