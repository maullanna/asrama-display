<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCondition extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'direction',
        'start_date',
        'end_date',
        'note',
        'reported_by_student_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'reported_by_student_id');
    }
}
