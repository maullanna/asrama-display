<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\StudentResource;
use App\Services\StudentImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListStudents extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Mahasiswa',
                'Halaman ini berisi data <strong>mahasiswa</strong> penghuni asrama: nama, NIM, foto, kamar yang ditempati, PIN mesin fingerprint, penanda ketua kamar, dan status aktif. Data di sinilah yang tampil di layar kiosk (TV).'
            ),
            Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Data Mahasiswa dari Excel')
                ->modalSubmitActionLabel('Import')
                ->form([
                    Forms\Components\Placeholder::make('petunjuk')
                        ->label('Format kolom')
                        ->content(new HtmlString(
                            'Baris pertama = header. Kolom: <b>nim, nama, pin, lantai, kamar, ketua</b>.<br>'
                            .'<span style="color:#64748b">pin kosong = pakai nim &middot; lantai = 1/2/3 &middot; kamar dibuat otomatis &middot; ketua = 1 bila ketua kamar.</span><br>'
                            .'<a href="'.route('students.import.template').'" style="color:#2563eb;text-decoration:underline">Unduh template Excel</a>'
                        )),
                    Forms\Components\FileUpload::make('file')
                        ->label('File Excel (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->storeFiles(false)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $file = is_array($data['file']) ? reset($data['file']) : $data['file'];
                    $result = app(StudentImporter::class)->import($file->getRealPath());

                    $body = "{$result['created']} baru, {$result['updated']} diperbarui, {$result['skipped']} dilewati.";

                    $notification = Notification::make()->title('Import selesai')->body($body);

                    if (! empty($result['errors'])) {
                        $notification->body($body.' Catatan: '.implode(' ', array_slice($result['errors'], 0, 5)))->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
