<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['grade_level_id', 'name'];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
}
