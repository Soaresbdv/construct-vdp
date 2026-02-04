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
        'image_url',
    ];
    
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
    public function views()
    {
        return $this->hasMany(ProjectView::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}