<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'student_id',
        'name',
        'relation',
        'phone',
        'email',
        'address',
        'image',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
