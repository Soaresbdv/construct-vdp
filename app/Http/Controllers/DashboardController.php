<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectView;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'in_progress')->count();
        $totalLeads = Lead::count();
        $totalViews = ProjectView::count();
        $period = $request->input('period', '6_months'); 
        $leadsData = [];

        if ($period === '30_days') {
            $leadsData = Lead::select(
                DB::raw('count(id) as count'), 
                DB::raw("TO_CHAR(created_at, 'DD/MM') as label")
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('label', DB::raw("DATE(created_at)"))
            ->orderBy(DB::raw("DATE(created_at)"))
            ->get();
        } else {
            $leadsData = Lead::select(
                DB::raw('count(id) as count'), 
                DB::raw("TO_CHAR(created_at, 'Mon') as label"),
                DB::raw("EXTRACT(MONTH FROM created_at) as month_num")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('label', 'month_num')
            ->orderBy('month_num')
            ->get();
        }

        $topProjects = Project::withCount('leads')
            ->orderBy('leads_count', 'desc')
            ->take(5)
            ->get()
            ->map(function($p) {
                return ['name' => $p->title, 'leads' => $p->leads_count];
            });

        return response()->json([
            'cards' => [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'total_leads' => $totalLeads,
                'total_views' => $totalViews
            ],
            'charts' => [
                'growth' => [
                    'labels' => $leadsData->pluck('label'),
                    'data' => $leadsData->pluck('count')
                ],
                'popularity' => [
                    'labels' => $topProjects->pluck('name'),
                    'data' => $topProjects->pluck('leads')
                ]
            ]
        ]);
    }
}