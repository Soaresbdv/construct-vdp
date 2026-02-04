<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectView extends Model
{
    use HasFactory;
    
    protected $fillable = ['project_id', 'ip_address', 'user_agent', 'user_id'];
}