<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispensal extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'medication_id',
        'symptoms',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
