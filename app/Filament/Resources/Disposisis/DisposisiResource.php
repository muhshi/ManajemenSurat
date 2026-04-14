<?php

namespace App\Filament\Resources\Disposisis;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Disposisis\Pages\ListDisposisis;
use App\Filament\Resources\Disposisis\Pages\CreateDisposisi;
use App\Filament\Resources\Disposisis\Pages\ViewDisposisi;
use App\Filament\Resources\Disposisis\Pages\EditDisposisi;
use App\Filament\Resources\DisposisiResource\Pages;
use App\Filament\Resources\DisposisiResource\RelationManagers;
use App\Models\Disposisi;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DisposisiResource extends Resource
{
    protected static ?string $model = Disposisi::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | \UnitEnum | null $navigationGroup = 'Surat Masuk';
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konteks Surat')
                    ->schema([
                        Select::make('surat_masuk_id')
                            ->relationship('suratMasuk', 'nomor_surat')
                            ->disabled(),
                        TextInput::make('sifat')
                            ->disabled(),
                    ])->columns(2),
                Section::make('Detail Disposisi')
                    ->schema([
                        Select::make('pengirim_id')
                            ->relationship('pengirim', 'name')
                            ->disabled(),
                        Select::make('penerima_id')
                            ->relationship('penerima', 'name')
                            ->disabled(),
                        Textarea::make('catatan')
                            ->columnSpanFull()
                            ->disabled(),
                        Select::make('status')
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
                TextColumn::make('suratMasuk.nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('suratMasuk.perihal')
                    ->label('Perihal')
                    ->limit(30)
                    ->tooltip(fn($state) => $state)
                    ->searchable(),
                TextColumn::make('pengirim.name')
                    ->label('Pemberi')
                    ->sortable(),
                TextColumn::make('penerima.name')
                    ->label('Penerima Staff')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Belum Dibaca' => 'danger',
                        'Dilihat' => 'warning',
                        'Selesai' => 'success',
                        default => 'gray',
                    })
                    ->action(
                        Action::make('updateStatus')
                            ->schema([
                                Select::make('status')
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
                TextColumn::make('created_at')
                    ->label('Tgl Disposisi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sifat')
                    ->options([
                        'Biasa' => 'Biasa',
                        'Segera' => 'Segera',
                        'Penting' => 'Penting',
                        'Rahasia' => 'Rahasia',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'Belum Dibaca' => 'Belum Dibaca',
                        'Dilihat' => 'Dilihat',
                        'Selesai' => 'Selesai',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListDisposisis::route('/'),
            'create' => CreateDisposisi::route('/create'),
            'view' => ViewDisposisi::route('/{record}'),
            'edit' => EditDisposisi::route('/{record}/edit'),
        ];
    }
}
