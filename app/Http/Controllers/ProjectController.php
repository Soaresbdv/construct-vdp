<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return Project::latest()->get();
    }
    
    public function show($id)
    {
        return \App\Models\Project::findOrFail($id);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'required|in:planning,in_progress,finished',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('projects', 'public');
            $validated['image_url'] = asset('storage/' . $path);
        }

        unset($validated['image']);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:planning,in_progress,finished',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('projects', 'public');
            $validated['image_url'] = asset('storage/' . $path);
            unset($validated['image']);
        }

        $project->update($validated);

        return response()->json($project);
    }
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        try {
            if ($project->image_url) {
                $path = str_replace(asset('storage/'), '', $project->image_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
        }

        $project->delete();

        return response()->json(null, 204);
    }
}