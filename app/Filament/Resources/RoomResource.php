<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Kamar';

    protected static ?string $modelLabel = 'Kamar';

    protected static ?string $pluralModelLabel = 'Kamar';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('floor_id')
                    ->label('Lantai')
                    ->relationship('floor', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('room_number')
                    ->label('Nomor Kamar')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('capacity')
                    ->label('Kapasitas')
                    ->required()
                    ->numeric()
                    ->default(8),
                Forms\Components\TextInput::make('access_pin')
                    ->label('PIN Akses Ketua Kamar')
                    ->maxLength(10),
                Forms\Components\ToggleButtons::make('status_color')
                    ->label('Status 5R kamar')
                    ->helperText('Penilaian kebersihan/kerapian kamar oleh super admin.')
                    ->inline()
                    ->options([
                        'hijau' => 'Hijau — Baik',
                        'kuning' => 'Kuning — On Progress',
                        'merah' => 'Merah — Berantakan',
                    ])
                    ->colors([
                        'hijau' => 'success',
                        'kuning' => 'warning',
                        'merah' => 'danger',
                    ])
                    ->icons([
                        'hijau' => 'heroicon-o-check-circle',
                        'kuning' => 'heroicon-o-clock',
                        'merah' => 'heroicon-o-exclamation-triangle',
                    ]),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('floor.name')
                    ->label('Lantai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('room_number')
                    ->label('Nomor Kamar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('access_pin')
                    ->label('PIN Akses')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_color')
                    ->label('Status 5R')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'hijau' => 'Hijau', 'kuning' => 'Kuning', 'merah' => 'Merah', default => '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'hijau' => 'success', 'kuning' => 'warning', 'merah' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('floor_id')
                    ->relationship('floor', 'name')
                    ->label('Lantai'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
