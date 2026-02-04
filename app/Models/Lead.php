<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'project_id', 'message', 'status'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}