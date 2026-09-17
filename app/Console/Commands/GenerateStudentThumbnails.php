<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\Thumbnailer;
use Illuminate\Console\Command;

class GenerateStudentThumbnails extends Command
{
    protected $signature = 'photos:thumbs {--force : Timpa thumbnail yang sudah ada}';

    protected $description = 'Buat thumbnail ringan untuk semua foto mahasiswa (untuk kiosk TV)';

    public function handle(): int
    {
        $students = Student::whereNotNull('photo_path')->get();
        $ok = 0;
        $fail = 0;

        $this->withProgressBar($students, function (Student $student) use (&$ok, &$fail) {
            $result = Thumbnailer::generate($student->photo_path, force: (bool) $this->option('force'));
            $result ? $ok++ : $fail++;
        });

        $this->newLine(2);
        $this->info("Thumbnail dibuat: {$ok} | gagal/dilewati: {$fail}");

        return self::SUCCESS;
    }
}
