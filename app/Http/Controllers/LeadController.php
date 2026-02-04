<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'project_id' => 'required|exists:projects,id',
            'message' => 'nullable|string'
        ]);

        $lead = Lead::create($validated);

        return response()->json($lead, 201);
    }

    // Listar leads (Apenas para Admin)
    public function index()
    {
        // Traz os leads com o nome da obra junto
        return Lead::with('project:id,title')->orderBy('created_at', 'desc')->get();
    }
}