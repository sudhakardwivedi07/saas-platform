<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fetch all reports (including soft-deleted) ordered by company, then creation time
        $reports = DB::table('reports')
            ->select('reports.id', 'reports.company_id', 'reports.created_at', 'companies.company_name')
            ->leftJoin('companies', 'companies.id', '=', 'reports.company_id')
            ->orderBy('reports.company_id')
            ->orderBy('reports.created_at')
            ->get();

        // Group by company_id and assign sequential analysis numbers
        $counters = [];
        foreach ($reports as $report) {
            $companyId = $report->company_id ?? 0;
            $counters[$companyId] = ($counters[$companyId] ?? 0) + 1;
            $n = $counters[$companyId];

            $companyName = trim($report->company_name ?? '');
            $newName = $companyName
                ? $companyName . ' (Analysis ' . $n . ')'
                : 'Analysis ' . $n;

            DB::table('reports')
                ->where('id', $report->id)
                ->update(['report_name' => $newName]);
        }
    }

    public function down(): void
    {
        // Not reversible — original names were timestamps with no semantic meaning
    }
};
