<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentConditionResource\Pages;
use App\Filament\Resources\StudentConditionResource\RelationManagers;
use App\Models\StudentCondition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentConditionResource extends Resource
{
    protected static ?string $model = StudentCondition::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Izin & Sakit';

    protected static ?string $modelLabel = 'Izin / Sakit';

    protected static ?string $pluralModelLabel = 'Izin & Sakit';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Mahasiswa')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                    ])
                    ->live()
                    ->required(),
                Forms\Components\Select::make('direction')
                    ->label('Arah (khusus izin)')
                    ->options([
                        'ci' => 'Check-in (CI)',
                        'co' => 'Check-out (CO)',
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'izin'),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Sampai (kosongkan jika masih berlangsung)'),
                Forms\Components\TextInput::make('note')
                    ->label('Catatan')
                    ->maxLength(255),
                Forms\Components\Select::make('reported_by_student_id')
                    ->relationship('reportedBy', 'name', fn (Builder $query) => $query->where('is_room_leader', true))
                    ->searchable()
                    ->preload()
                    ->label('Dilaporkan oleh (ketua kamar)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Mahasiswa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state) => $state === 'sakit' ? 'danger' : 'info'),
                Tables\Columns\TextColumn::make('direction')
                    ->label('Arah'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Dilaporkan oleh')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
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
            'index' => Pages\ListStudentConditions::route('/'),
            'create' => Pages\CreateStudentCondition::route('/create'),
            'edit' => Pages\EditStudentCondition::route('/{record}/edit'),
        ];
    }
}
