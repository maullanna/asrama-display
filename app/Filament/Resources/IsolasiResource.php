<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsByRole;
use App\Filament\Resources\IsolasiResource\Pages;
use App\Models\StudentCondition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IsolasiResource extends Resource
{
    use RestrictsByRole;

    protected static ?string $model = StudentCondition::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Ruang Isolasi';

    protected static ?string $modelLabel = 'Isolasi';

    protected static ?string $pluralModelLabel = 'Ruang Isolasi';

    protected static ?int $navigationSort = 6;

    protected static function allowedRoles(): array
    {
        return ['super_admin', 'menkes'];
    }

    /** Hanya tampilkan kondisi berjenis isolasi. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'isolasi');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Mahasiswa')
                ->relationship('student', 'name', fn (Builder $query) => $query->where('is_active', true))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Textarea::make('note')
                ->label('Detail sakit')
                ->placeholder('mis. Demam tinggi, observasi 2 hari')
                ->rows(2)
                ->maxLength(255),
            Forms\Components\DatePicker::make('start_date')
                ->label('Masuk isolasi')
                ->default(today())
                ->required(),
            Forms\Components\DatePicker::make('end_date')
                ->label('Keluar isolasi (kosongkan bila masih di dalam)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kamar_asal')
                    ->label('Kamar asal')
                    ->getStateUsing(fn ($record) => $record->student?->room
                        ? (($record->student->room->floor?->name ?? '-').' - '.$record->student->room->room_number)
                        : '-'),
                Tables\Columns\TextColumn::make('note')
                    ->label('Detail sakit')
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Masuk')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Keluar')
                    ->date()
                    ->placeholder('masih isolasi'),
            ])
            ->defaultSort('start_date', 'desc')
            ->actions([
                Tables\Actions\Action::make('selesai')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => is_null($record->end_date))
                    ->requiresConfirmation()
                    ->modalHeading('Keluarkan dari isolasi?')
                    ->action(fn ($record) => $record->update(['end_date' => today()])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIsolasi::route('/'),
            'create' => Pages\CreateIsolasi::route('/create'),
            'edit' => Pages\EditIsolasi::route('/{record}/edit'),
        ];
    }
}
