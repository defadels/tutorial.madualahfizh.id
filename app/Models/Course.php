<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }
} 