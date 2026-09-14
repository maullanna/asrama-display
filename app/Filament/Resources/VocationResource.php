<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsByRole;
use App\Filament\Resources\VocationResource\Pages;
use App\Models\Vocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VocationResource extends Resource
{
    use RestrictsByRole;

    protected static ?string $model = Vocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Vokasi';

    protected static ?string $modelLabel = 'Mahasiswa Vokasi';

    protected static ?string $pluralModelLabel = 'Vokasi';

    protected static ?int $navigationSort = 7;

    protected static function allowedRoles(): array
    {
        return ['super_admin', 'koordinator'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('location')
                ->label('Lokasi Vokasi')
                ->options(Vocation::LOCATIONS)
                ->native(false)
                ->required(),
            Forms\Components\FileUpload::make('photo_path')
                ->label('Foto')
                ->image()
                ->directory('vocations')
                ->visibility('public'),
            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Vocation::LOCATIONS[$state] ?? $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->label('Lokasi')
                    ->options(Vocation::LOCATIONS),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVocations::route('/'),
            'create' => Pages\CreateVocation::route('/create'),
            'edit' => Pages\EditVocation::route('/{record}/edit'),
        ];
    }
}
