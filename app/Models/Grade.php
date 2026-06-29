<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'assignment',
        'midterm',
        'final',
        'total',
        'grade',
    ];

    protected $casts = [
        'assignment' => 'decimal:2',
        'midterm' => 'decimal:2',
        'final' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
