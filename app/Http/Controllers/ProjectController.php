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
    
    public function show($id)
    {
        // O findOrFail retorna 404 automático se o ID não existir
        return \App\Models\Project::findOrFail($id);
    }
}