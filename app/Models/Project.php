<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'location',
        'status',
        'image_url',
        'start_date',
        'end_date',
    ];
    
    // Opcional: Casts para garantir que datas venham formatadas corretamente
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}