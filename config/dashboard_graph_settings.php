<?php

return [
    'analyze_url'           => env('INSIGHTS_ANALYZE_URL'),
    'synopsis_api_url'      => env('SYNOPSIS_API_URL'),
    'default_style'         => env('INSIGHTS_DEFAULT_STYLE', 'plain'),
    'monthly_insight_limit' => (int) env('INSIGHTS_MONTHLY_LIMIT', 5),

    'graph_documents' => [
        1 => [3,4],
        2 => [3,6],
        3 => [4],
        4 => [6],
        5 => [6],
        6 => [6],
        7 => [2,6],
        8 => [1,6],
        9 => [2],
    ],
    'synopsis_api_kpis' => [
        1 => 'cash_burn_runway',
        2 => 'working_capital_position',
        3 => 'operating_cash_flow',
        4 => 'monthly_revenue_trend',
        5 => 'ebitda_trend',
        6 => 'margin_trends',
        7 => 'ar_health',
        8 => 'ap_leverage',
        9 => 'revenue_concentration',
    ],
];
