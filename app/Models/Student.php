<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'lrn',
        'name',
        'section_id',
        'guardian_name',
        'guardian_contact'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function dispensals()
    {
        return $this->hasMany(Dispensal::class);
    }
}
