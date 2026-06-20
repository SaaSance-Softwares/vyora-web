<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchQuery;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SearchQueryController extends Controller
{
    public function index(Request $request)
    {
        $queries = SearchQuery::orderBy('updated_at', 'desc')->paginate(50);
        return view('admin.marketing.search-queries', compact('queries'));
    }

    public function deleteByDate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end   = Carbon::parse($request->end_date)->endOfDay();

        $deleted = SearchQuery::whereBetween('created_at', [$start, $end])->delete();

        return redirect()->route('admin.online-store.marketing.search-queries.index')
            ->with('success', "Successfully deleted {$deleted} search queries from the selected date range.");
    }

    public function export(Request $request)
    {
        $filename = "search_queries_" . date('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $queries = SearchQuery::orderBy('updated_at', 'desc')->get();

        $callback = function() use($queries) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Search Query', 'Search Count', 'Results Found', 'Last Searched At']);

            foreach ($queries as $q) {
                fputcsv($file, [
                    $q->id,
                    $q->query,
                    $q->count,
                    $q->results_count,
                    $q->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
