{{-- Shared HTML builder for KPI detail (drawer + generic full page). Pure JS. --}}
<script>
(function (w) {
    function esc(x) {
        if (x === null || x === undefined) return '';
        return String(x)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function detailsGrid(pairs) {
        var h = '<div class="kpi-detail-metric-grid">';
        pairs.forEach(function (p) {
            h += '<div class="kpi-detail-metric"><span class="kpi-detail-metric-l">' + esc(p.l) + '</span><span class="kpi-detail-metric-v">' + esc(p.v) + '</span></div>';
        });
        h += '</div>';
        return h;
    }

    function tbl(rows, headers) {
        var h = '<table class="kpi-detail-table"><thead><tr>';
        headers.forEach(function (x) { h += '<th>' + esc(x) + '</th>'; });
        h += '</tr></thead><tbody>';
        rows.forEach(function (r) {
            h += '<tr>';
            r.forEach(function (c) { h += '<td>' + esc(c) + '</td>'; });
            h += '</tr>';
        });
        h += '</tbody></table>';
        return h;
    }

    w.__kpiBuildDetailMarkup = function (kpiType, data) {
        var d = data || {};
        switch (kpiType) {
            case 'runway-burn': {
                var burn = d.burn_type === 'burn' ? 'Burning cash' : (d.burn_type === 'build' ? 'Building cash' : (d.burn_type || '—'));
                return detailsGrid([
                    { l: 'Runway (months)', v: d.runway_months ?? '—' },
                    { l: 'Cash balance', v: d.cash_balance ?? '—' },
                    { l: 'Monthly burn', v: d.monthly_burn ?? '—' },
                    { l: 'Burn profile', v: burn },
                ]);
            }
            case 'working-capital':
                return detailsGrid([
                    { l: 'Working capital', v: d.working_capital ?? '—' },
                    { l: 'Current assets', v: d.current_assets ?? '—' },
                    { l: 'Current liabilities', v: d.current_liabilities ?? '—' },
                    { l: 'MoM change', v: d.mom_percent ?? '—' },
                ]);
            case 'operating-cashflow': {
                var md = d.monthly_data || [];
                var rows = md.slice(-8).map(function (pt) {
                    return [pt.month || '—', pt.amount || '—'];
                });
                return detailsGrid([
                    { l: 'Data points', v: String(md.length || 0) },
                ]) + (rows.length ? '<h4 class="kpi-detail-subh">Recent months</h4>' + tbl(rows, ['Month', 'Amount']) : '');
            }
            case 'revenue':
                return detailsGrid([
                    { l: 'Current month revenue', v: d.current_month_revenue ?? '—' },
                    { l: 'YoY growth', v: d.yoy_growth ?? '—' },
                ]);
            case 'ebitda':
                return detailsGrid([
                    { l: 'EBITDA', v: d.ebitda ?? '—' },
                    { l: 'Margin', v: d.margin ?? '—' },
                ]);
            case 'margin-trends': {
                var m = d.monthly_data || [];
                var latest = m.length ? m[m.length - 1] : null;
                var rows = m.slice(-8).map(function (pt) {
                    return [
                        pt.month || '—',
                        pt.gross_margin != null ? pt.gross_margin + '%' : '—',
                        pt.net_margin != null ? pt.net_margin + '%' : '—',
                    ];
                });
                return detailsGrid([
                    { l: 'Periods', v: String(m.length || 0) },
                    { l: 'Latest gross margin', v: latest && latest.gross_margin != null ? latest.gross_margin + '%' : '—' },
                    { l: 'Latest net margin', v: latest && latest.net_margin != null ? latest.net_margin + '%' : '—' },
                ]) + (rows.length ? '<h4 class="kpi-detail-subh">Recent periods</h4>' + tbl(rows, ['Month', 'Gross', 'Net']) : '');
            }
            case 'ar-aging': {
                var buckets = d.aging_buckets || [];
                var rows = buckets.map(function (b) {
                    return [b.label || '—', b.amount || '—', b.percentage || '—'];
                });
                return detailsGrid([
                    { l: 'Total outstanding', v: d.total_ar ?? '—' },
                    { l: 'DSO', v: d.dso_badge ?? '—' },
                ]) + (rows.length ? '<h4 class="kpi-detail-subh">Aging buckets</h4>' + tbl(rows, ['Bucket', 'Amount', 'Share']) : '');
            }
            case 'ap-leverage': {
                var buckets = d.aging_buckets || [];
                var rows = buckets.map(function (b) {
                    return [b.label || '—', b.amount || '—', b.percentage || '—'];
                });
                return detailsGrid([
                    { l: 'Total payables', v: d.total_ap ?? '—' },
                    { l: 'Days payable', v: d.dpo_badge || '—' },
                ]) + (rows.length ? '<h4 class="kpi-detail-subh">Aging buckets</h4>' + tbl(rows, ['Bucket', 'Amount', 'Share']) : '');
            }
            case 'revenue-concentration': {
                var cust = (d.customers || []).filter(function (c) {
                    return String(c.name || '').toLowerCase() !== 'others';
                }).slice(0, 8);
                var rows = cust.map(function (c) {
                    return [c.name || '—', c.revenue || '—', c.percentage || '—'];
                });
                return detailsGrid([
                    { l: 'Top 5 combined (model)', v: d.top5_percentage ?? '—' },
                ]) + (rows.length ? '<h4 class="kpi-detail-subh">Customers</h4>' + tbl(rows, ['Customer', 'Revenue', '% of total']) : '');
            }
            default:
                return '<p class="kpi-detail-fallback">No extended layout for <strong>' + esc(kpiType) + '</strong>. Use <em>Open full page</em> if available.</p>';
        }
    };
})(window);
</script>
