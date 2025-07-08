<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'video_url',
        'duration',
        'order',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
} 