<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        // Retorna todas as obras, ordenadas pelas mais recentes
        return Project::latest()->get();
    }
}