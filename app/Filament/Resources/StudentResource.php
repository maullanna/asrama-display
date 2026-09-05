<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('room_id')
                    ->relationship('room', 'room_number')
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\Room $record) => "{$record->floor->name} - Kamar {$record->room_number}")
                    ->searchable()
                    ->preload()
                    ->label('Kamar'),
                Forms\Components\TextInput::make('student_code')
                    ->label('NIM')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('device_pin')
                    ->label('PIN mesin fingerprint')
                    ->maxLength(20),
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Foto')
                    ->image()
                    ->directory('students')
                    ->visibility('public'),
                Forms\Components\Toggle::make('is_room_leader')
                    ->label('Ketua kamar')
                    ->default(false)
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
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
                Tables\Columns\TextColumn::make('student_code')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Kamar')
                    ->formatStateUsing(fn ($record) => $record->room ? "{$record->room->floor->name} - {$record->room->room_number}" : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_pin')
                    ->label('PIN')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_room_leader')
                    ->label('Ketua kamar')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('room_id')
                    ->relationship('room', 'room_number')
                    ->label('Kamar'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
