<?php

namespace App\Filament\Resources\VocationResource\Pages;

use App\Filament\Concerns\HasPageInfo;
use App\Filament\Resources\VocationResource;
use App\Services\VocationImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListVocations extends ListRecords
{
    use HasPageInfo;

    protected static string $resource = VocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->infoAction(
                'Tentang Halaman Vokasi',
                'Halaman ini untuk data <strong>mahasiswa vokasi</strong> (kelompok A10) yang sedang pelatihan di luar asrama. Cukup isi nama, foto, dan lokasi (Sunter / Karawang). Data ini tampil di slide VOKASI pada layar kiosk, dikelompokkan per lokasi.'
            ),
            Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Data Vokasi dari Excel')
                ->modalSubmitActionLabel('Import')
                ->form([
                    Forms\Components\Placeholder::make('petunjuk')
                        ->label('Format kolom')
                        ->content(new HtmlString(
                            'Baris pertama = header. Kolom: <b>nama, lokasi</b>.<br>'
                            .'<span style="color:#64748b">lokasi diisi <b>Sunter</b> atau <b>Karawang</b>. Foto diupload terpisah lewat panel.</span><br>'
                            .'<a href="'.route('vocations.import.template').'" style="color:#2563eb;text-decoration:underline">Unduh template Excel</a>'
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
                    $result = app(VocationImporter::class)->import($file->getRealPath());

                    $body = "{$result['created']} baru, {$result['skipped']} dilewati.";

                    $notification = Notification::make()->title('Import selesai')->body($body);

                    if (! empty($result['errors'])) {
                        $notification->body($body.' Catatan: '.implode(' ', array_slice($result['errors'], 0, 5)))->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();
                }),
            Actions\CreateAction::make()->label('Tambah Mahasiswa Vokasi'),
        ];
    }
}
