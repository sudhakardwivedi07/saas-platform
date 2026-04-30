<?php

return [
    'analyze_url'           => env('INSIGHTS_ANALYZE_URL', 'https://demo.incubation.ai/aicfo-short/analyze'),
    'default_style'         => env('INSIGHTS_DEFAULT_STYLE', 'plain'),
    'monthly_insight_limit' => (int) env('INSIGHTS_MONTHLY_LIMIT', 5),
];
