<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceLogResource\Pages;
use App\Filament\Resources\AttendanceLogResource\RelationManagers;
use App\Models\AttendanceLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceLogResource extends Resource
{
    protected static ?string $model = AttendanceLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'Log Absensi';

    protected static ?string $modelLabel = 'Log Absensi';

    protected static ?string $pluralModelLabel = 'Log Absensi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Mahasiswa'),
                Forms\Components\TextInput::make('device_pin')
                    ->label('PIN dari mesin')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('device_sn')
                    ->label('Serial number mesin')
                    ->maxLength(40),
                Forms\Components\DateTimePicker::make('scanned_at')
                    ->label('Waktu scan')
                    ->required(),
                Forms\Components\Select::make('direction')
                    ->label('Arah')
                    ->options([
                        'ci' => 'Masuk (CI)',
                        'co' => 'Keluar (CO)',
                    ])
                    ->default('ci')
                    ->required(),
                Forms\Components\Select::make('method')
                    ->label('Metode')
                    ->options([
                        'fingerprint' => 'Sidik Jari',
                        'qr' => 'QR',
                        'manual' => 'Manual',
                    ])
                    ->default('fingerprint')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Mahasiswa')
                    ->default('(belum terdaftar)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_pin')
                    ->label('PIN')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Waktu scan')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'ci' ? 'Masuk' : 'Keluar')
                    ->color(fn (string $state) => $state === 'ci' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('method')
                    ->label('Metode')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'fingerprint' => 'Sidik Jari',
                        'qr' => 'QR',
                        'manual' => 'Manual',
                        default => $state,
                    })
                    ->searchable(),
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
            ->defaultSort('scanned_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('Arah')
                    ->options([
                        'ci' => 'Masuk (CI)',
                        'co' => 'Keluar (CO)',
                    ]),
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
            'index' => Pages\ListAttendanceLogs::route('/'),
            'create' => Pages\CreateAttendanceLog::route('/create'),
            'edit' => Pages\EditAttendanceLog::route('/{record}/edit'),
        ];
    }
}
