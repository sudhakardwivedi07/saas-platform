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
        var h = '<div class="kpi-detail-metrics-row">';
        pairs.forEach(function (p) {
            h += '<div class="kpi-detail-metric-card"><span class="lbl">' + esc(p.l) + '</span><div class="val">' + esc(p.v) + '</div>' + (p.s ? '<div class="sub">' + esc(p.s) + '</div>' : '') + '</div>';
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

    function bulletList(items) {
        return '<ul>' + (items || []).map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</ul>';
    }

    function normalizeNumber(x) {
        if (x === null || x === undefined) return 0;
        if (typeof x === 'number') return x;
        var n = parseFloat(String(x).replace(/[^0-9.-]/g, ''));
        return isFinite(n) ? n : 0;
    }

    function monthShort(v) {
        if (!v) return '';
        var s = String(v);
        if (/^\d{4}-\d{2}$/.test(s)) {
            var p = s.split('-');
            var m = parseInt(p[1], 10) - 1;
            return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][m] || s;
        }
        return s;
    }

    function barChart(items, valueKey, labelKey, maxBars, negAware) {
        var rows = (items || []).slice(0, maxBars || 12);
        var maxAbs = 1;
        rows.forEach(function (r) { maxAbs = Math.max(maxAbs, Math.abs(normalizeNumber(r[valueKey]))); });
        var h = '<div class="kpi-mini-chart">';
        rows.forEach(function (r) {
            var raw = normalizeNumber(r[valueKey]);
            var pct = Math.max(2, Math.round((Math.abs(raw) / maxAbs) * 100));
            var negClass = negAware && raw < 0 ? ' neg' : '';
            h += '<div class="kpi-chart-col"><div class="kpi-chart-bar-wrap"><div class="kpi-chart-bar' + negClass + '" style="height:' + pct + '%"></div></div><div class="kpi-chart-label">' + esc(monthShort(r[labelKey])) + '</div></div>';
        });
        h += '</div>';
        return h;
    }

    function chartCard(title, subtitle, chartHtml) {
        return '<div class="kpi-section-card"><h4>' + esc(title) + '</h4><p>' + esc(subtitle || '') + '</p>' + chartHtml + '</div>';
    }

    function explainCards(items) {
        var h = '<div class="kpi-section-card"><h4>Understanding ' + esc(items.title || 'KPI') + '</h4><p>Key concepts behind this metric.</p><div class="kpi-explain-grid">';
        (items.cards || []).forEach(function (c, i) {
            h += '<div class="kpi-explain-card' + (i % 2 ? ' alt' : '') + '"><h5>' + esc(c.h) + '</h5><p>' + esc(c.b) + '</p></div>';
        });
        h += '</div></div>';
        return h;
    }

    function detailPage(title, subtitle, metrics, strengths, attention, actions, sections) {
        return ''
            + '<div class="kpi-detail-page">'
            +   '<div class="kpi-detail-page-head"><div class="kpi-detail-breadcrumb">Dashboard  >  ' + esc(title.replace('Analysis', 'Details')) + '</div><h2>' + esc(title) + '</h2><p>' + esc(subtitle) + '</p></div>'
            +   detailsGrid(metrics)
            +   '<div class="kpi-detail-synopsis">'
            +      '<h3>KPI Synopsis: ' + esc(title) + '</h3>'
            +      '<p>Executive interpretation based on available KPI data.</p>'
            +      '<div id="kpiSynopsisLoading" style="text-align:center;padding:28px 16px;">'
            +         '<div style="display:inline-block;width:2.4rem;height:2.4rem;border:3px solid #e5e7eb;border-top-color:#0788be;border-radius:50%;animation:kpiSpin 0.8s linear infinite;"></div>'
            +         '<p style="margin-top:12px;color:#6c757d;font-size:13px;margin-bottom:0;">Generating KPI Synopsis...</p>'
            +      '</div>'
            +      '<div id="kpiSynopsisError" style="display:none;padding:14px 12px;background:#fff5f5;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:13px;">'
            +         '<strong>Synopsis unavailable.</strong> We could not generate the synopsis at this time. Please try again later.'
            +      '</div>'
            +      '<div class="kpi-detail-synopsis-grid" id="kpiSynopsisCards" style="display:none;">'
            +         '<div class="kpi-detail-note kpi-note-strength"><h4>Key Strengths</h4><div id="kpiSynopsisStrength" style="color:#4b5563;font-size:11px;line-height:1.6;"></div></div>'
            +         '<div class="kpi-detail-note kpi-note-attention"><h4>Areas of Attention</h4><div id="kpiSynopsisAttention" style="color:#4b5563;font-size:11px;line-height:1.6;"></div></div>'
            +         '<div class="kpi-detail-note kpi-note-actions"><h4>Recommended Actions</h4><div id="kpiSynopsisActions" style="color:#4b5563;font-size:11px;line-height:1.6;"></div></div>'
            +      '</div>'
            +   '</div>'
            +   (sections || '')
            + '</div>';
    }

    w.__kpiBuildDetailMarkup = function (kpiType, data) {
        var d = data || {};
        switch (kpiType) {
            case 'runway-burn': {
                var burn = d.burn_type === 'burn' ? 'Burn' : (d.burn_type === 'build' ? 'Build' : (d.burn_type || '—'));
                var rbRows = (d.monthly_data || []).map(function (x) {
                    var raw = normalizeNumber(x.raw_amount);
                    return [monthShort(x.month), x.amount || '—', raw < 0 ? 'Burn' : (raw > 0 ? 'Build' : 'Neutral'), d.cash_balance || '—'];
                });
                return detailPage('Runway & Burn Analysis', 'Liquidity runway and burn dynamics.',
                    [
                        { l: 'Cash Balance', v: d.cash_balance ?? '—' },
                        { l: 'Net Monthly Cash Burn', v: d.monthly_burn ?? '—', s: 'Average negative burn months' },
                        { l: 'Cash Runway', v: d.runway_months ?? '—', s: 'Months' },
                        { l: 'Health Status', v: burn, s: 'Cash trajectory' },
                    ],
                    ['Cash movement has shown improvement with stronger recent periods.', 'Existing liquidity provides near-term resilience.', 'Trend indicates stronger operational support.'],
                    ['Monthly burn consistency must be monitored closely.', 'Seasonal swings may alter runway direction quickly.', 'Burn spikes can compress cash headroom.'],
                    ['Maintain 13-week cash forecasting cadence.', 'Prioritize conversion of cash-positive initiatives.', 'Trigger alerts for burn-rate threshold breaches.'],
                    chartCard('12-Month Cash Flow Trend', 'Visual profile of cash build/burn behavior over time.', barChart((d.monthly_data || []), 'raw_amount', 'month', 12, true))
                    + '<div class="kpi-section-card"><h4>Monthly Breakdown</h4><p>Detailed month-wise activity summary.</p>'
                    + tbl(rbRows, ['Month', 'Net Change in Cash', 'Status', 'Cumulative'])
                    + '</div>'
                    + explainCards({ title: 'Cash Flow', cards: [
                        { h: 'Operating Activities', b: 'Core business cash generated from operations and collections.' },
                        { h: 'Investing Activities', b: 'Cash used for assets, capex, and long-term investments.' },
                        { h: 'Financing Activities', b: 'Cash from equity, borrowings, debt service, or dividends.' }
                    ]})
                );
            }
            case 'working-capital': {
                var wcCalcRows = [
                    ['Current Assets', d.current_assets || '—', 'Cash, receivables, inventory'],
                    ['Current Liabilities', d.current_liabilities || '—', 'Payables, short-term debt'],
                    ['= Working Capital', d.working_capital || '—', 'Positive = healthy liquidity'],
                ];
                return detailPage('Working Capital Analysis', 'Liquidity buffer across current assets and liabilities.',
                    [
                        { l: 'Working Capital', v: d.working_capital ?? '—', s: (d.mom_percent || '—') + ' vs last month' },
                        { l: 'Current Assets', v: d.current_assets ?? '—', s: 'Total short-term resources' },
                        { l: 'Current Liabilities', v: d.current_liabilities ?? '—', s: 'Total short-term obligations' },
                        { l: 'MoM Change', v: d.mom_percent ?? '—', s: d.mom_change || '—' },
                    ],
                    ['Strong working capital indicates healthy liquidity cushion.', 'Asset base currently covers liability burden effectively.', 'Cash visibility supports short-term operations.'],
                    ['Liability timing shifts can impact net liquidity quickly.', 'Receivable collection speed remains a key watchpoint.', 'Monthly variance can mask underlying pressure.'],
                    ['Set minimum liquidity threshold guardrails.', 'Track working-capital drivers weekly.', 'Run scenario plans for cash and obligations.'],
                    chartCard('Assets vs Liabilities Balance', 'Visual comparison of current position.', barChart([
                        { month: 'Assets', raw_amount: normalizeNumber(d.assets_share_pct) },
                        { month: 'Liabilities', raw_amount: normalizeNumber(d.liabilities_share_pct) }
                    ], 'raw_amount', 'month', 2, false))
                    + '<div class="kpi-section-card"><h4>Working Capital Calculation</h4><p>Breakdown of your working capital.</p>'
                    + tbl(wcCalcRows, ['Line Item', 'Amount', 'Notes'])
                    + '</div>'
                    + explainCards({ title: 'Working Capital', cards: [
                        { h: 'Current Assets', b: 'Resources expected to convert to cash within one year.' },
                        { h: 'Current Liabilities', b: 'Obligations due within one year that require near-term cash.' }
                    ]})
                );
            }
            case 'operating-cashflow': {
                var md = d.monthly_data || [];
                var ocfRows = md.slice(0, 12).map(function (x) { return [monthShort(x.month), x.amount, normalizeNumber(x.raw_amount) >= 0 ? 'Positive' : 'Negative']; });
                var t12 = 0;
                md.forEach(function (x) { t12 += normalizeNumber(x.raw_amount); });
                var peak = '$0';
                var peakRaw = 0;
                md.forEach(function (x) {
                    var raw = normalizeNumber(x.raw_amount);
                    if (raw >= peakRaw) {
                        peakRaw = raw;
                        peak = x.amount || peak;
                    }
                });
                return detailPage('Operating Cash Flow Analysis', 'Operating cash generation trend (L12M).',
                    [
                        { l: 'Operating Cash Flow (LTM)', v: '$' + Math.round(t12).toLocaleString(), s: 'Sum of Operating column' },
                        { l: 'Peak (12-mo)', v: peak, s: 'Highest monthly operating CF' },
                        { l: 'Trend Direction', v: t12 >= 0 ? 'Improving' : 'Declining', s: 'Operating trend' },
                        { l: 'Data Points', v: String(md.length || 0), s: 'Available periods' },
                    ],
                    ['Cash inflow strength is visible across recent periods.', 'Positive months indicate improving operational coverage.', 'Trendline supports planning for reinvestment.'],
                    ['Negative clusters can pressure near-term liquidity.', 'Volatility may impact commitment planning.', 'Sustained downside requires corrective action quickly.'],
                    ['Review cash drivers by period and owner.', 'Set trigger thresholds for intervention.', 'Use rolling forecast linked to OCF variance.'],
                    chartCard('12-Month Operating Cash Flow Trend', 'Visual overview of operating cash inflows/outflows over time.', barChart(md, 'raw_amount', 'month', 12, true))
                    + '<div class="kpi-section-card"><h4>Monthly Breakdown</h4><p>Detailed cash flow month-wise performance table.</p>'
                    + tbl(ocfRows, ['Month', 'Operating Cash Flow', 'Status'])
                    + '</div>'
                    + explainCards({ title: 'Operating Cash Flow', cards: [
                        { h: 'Positive OCF', b: 'Positive OCF means operations generate cash to support growth.' },
                        { h: 'Negative OCF', b: 'Negative OCF means operations consumed cash and need attention.' },
                        { h: 'T12M Trend', b: 'Twelve-month trend smooths seasonality to show operating health.' }
                    ]})
                );
            }
            case 'revenue': {
                var trend = d.monthly_trend || [];
                var revRows = trend.map(function (x) { return [monthShort(x.month), '$' + normalizeNumber(x.current_year).toLocaleString(), '$' + normalizeNumber(x.last_year).toLocaleString()]; });
                return detailPage('Revenue Analysis', 'Topline performance and growth momentum.',
                    [
                        { l: 'Last 12 Months Total', v: '$' + trend.reduce(function (acc, x) { return acc + normalizeNumber(x.current_year); }, 0).toLocaleString() },
                        { l: 'Current Month Revenue', v: d.current_month_revenue ?? '—' },
                        { l: 'Average Monthly', v: '$' + Math.round(trend.reduce(function (acc, x) { return acc + normalizeNumber(x.current_year); }, 0) / Math.max(1, trend.length)).toLocaleString() },
                        { l: 'Peak Month', v: '$' + Math.max.apply(Math, trend.map(function (x) { return normalizeNumber(x.current_year); }).concat([0])).toLocaleString() },
                    ],
                    ['Revenue momentum is visible in monthly pattern.', 'Peak windows indicate timing for strongest demand.', 'Trend offers baseline for capacity planning.'],
                    ['Mid-period dips may impact run-rate assumptions.', 'Inconsistent trend can reduce forecast confidence.', 'Concentration of revenue spikes increases sensitivity.'],
                    ['Track YoY and MoM growth in parallel.', 'Push campaigns around historically strong months.', 'Address weak months with targeted pipeline actions.'],
                    chartCard('Year-over-year revenue comparison and growth analysis.', 'Revenue trend with yearly comparison.', barChart(trend.map(function (x) { return { month: x.month, raw_amount: normalizeNumber(x.current_year) }; }), 'raw_amount', 'month', 12, false))
                    + '<div class="kpi-section-card"><h4>Monthly Revenue Breakdown</h4><p>Detailed monthly revenue with year-over-year comparison.</p>'
                    + tbl(revRows, ['Month', 'Revenue', 'Last Year'])
                    + '</div>'
                    + explainCards({ title: 'Revenue', cards: [
                        { h: 'Current Year', b: 'Tracks current-period realized topline performance.' },
                        { h: 'Last Year', b: 'Provides baseline context for growth comparison.' }
                    ]})
                );
            }
            case 'ebitda': {
                var eb = d.monthly_bars_raw || [];
                var eRows = eb.slice(0, 12).map(function (v, i) { return ['M' + (i + 1), '$' + normalizeNumber(v).toLocaleString(), '$0.00', '$0.00', '$' + normalizeNumber(v).toLocaleString(), d.margin || '—']; });
                return detailPage('EBITDA Analysis', 'Core operating profitability snapshot.',
                    [
                        { l: 'Total EBITDA L12M', v: d.ebitda ?? '—' },
                        { l: 'Average Monthly', v: '$' + Math.round(eb.reduce(function (a, b) { return a + normalizeNumber(b); }, 0) / Math.max(1, eb.length)).toLocaleString() },
                        { l: 'EBITDA Margin', v: d.margin ?? '—' },
                        { l: 'Positive Months', v: eb.filter(function (x) { return normalizeNumber(x) > 0; }).length + '/' + (eb.length || 0) },
                    ],
                    ['Consistent EBITDA periods indicate improving operating quality.', 'Healthy margin supports reinvestment and resilience.', 'Run-rate visibility assists planning decisions.'],
                    ['Weak months can materially reduce annual EBITDA quality.', 'Cost pressure may dilute margin if unmanaged.', 'Profitability volatility can impact confidence in guidance.'],
                    ['Review cost drivers by month and category.', 'Protect margin through pricing/efficiency actions.', 'Set alerts for abnormal EBITDA swings.'],
                    chartCard('Monthly EBITDA Trend', 'Visual overview of EBITDA performance over time.', barChart(eb.map(function (v, i) { return { month: 'M' + (i + 1), raw_amount: normalizeNumber(v) }; }), 'raw_amount', 'month', 12, false))
                    + '<div class="kpi-section-card"><h4>Monthly EBITDA Breakdown</h4><p>Detailed monthly EBITDA performance.</p>'
                    + tbl(eRows, ['Month', 'Revenue', 'COGS', 'OPEX', 'EBITDA', 'Margin'])
                    + '</div>'
                    + explainCards({ title: 'EBITDA', cards: [
                        { h: 'Revenue', b: 'Total recognized topline used in EBITDA computation.' },
                        { h: 'Operating Expenses', b: 'Recurring operating costs excluding financing and taxes.' }
                    ]})
                );
            }
            case 'margin-trends': {
                var m = d.monthly_data || [];
                var latest = m.length ? m[m.length - 1] : null;
                var grossVal = d.gross_margin_ltm != null ? d.gross_margin_ltm : (latest ? latest.gross_margin : null);
                var netVal = d.net_margin_ltm != null ? d.net_margin_ltm : (latest ? latest.net_margin : null);
                var mRows = m.map(function (x) {
                    return [
                        monthShort(x.month),
                        x.gross_margin != null ? x.gross_margin + '%' : '—',
                        x.net_margin != null ? x.net_margin + '%' : '—'
                    ];
                });
                return detailPage('Margin Trends Analysis', 'Gross and net margin trajectory over time.',
                    [
                        { l: 'Gross Margin', v: grossVal != null ? grossVal + '%' : '—' },
                        { l: 'Net Margin', v: netVal != null ? netVal + '%' : '—' },
                        { l: 'Periods', v: String(m.length || 0) },
                        { l: 'Basis', v: 'LTM Revenue Months' },
                    ],
                    ['Margin stability signals disciplined pricing and cost control.', 'Gross vs net spread provides operating leverage insight.', 'Trend offers early view into profitability direction.'],
                    ['Spread widening can indicate rising overhead pressure.', 'Net margin softness may follow gross margin decline.', 'Volatile months can hide structural margin issues.'],
                    ['Set margin guardrails by segment.', 'Review months with largest spread changes.', 'Tie action plans to margin compression triggers.'],
                    chartCard('Margin Trends Over Time', 'Gross and net margin trajectory.', barChart(m.map(function (x) { return { month: x.month, raw_amount: normalizeNumber(x.gross_margin) }; }), 'raw_amount', 'month', 12, false))
                    + '<div class="kpi-section-card"><h4>Monthly Margin Breakdown</h4><p>Detailed monthly margin and profitability metrics.</p>'
                    + tbl(mRows, ['Month', 'Gross Margin', 'Net Margin'])
                    + '</div>'
                    + explainCards({ title: 'Margins', cards: [
                        { h: 'Gross Margin', b: 'Revenue minus direct costs, expressed as percentage.' },
                        { h: 'Net Profit Margin', b: 'Net income as percentage of revenue after all expenses.' }
                    ]})
                );
            }
            case 'ar-aging': {
                var arBuckets = d.aging_buckets || [];
                var arRows = arBuckets.map(function (x) { return [x.label, x.amount, x.percentage]; });
                return detailPage('AR Aging Analysis', 'Receivables quality and collection velocity.',
                    [
                        { l: 'Total Outstanding', v: d.total_ar ?? '—' },
                        { l: 'DSO', v: d.dso_badge ?? '—' },
                        { l: 'Total Overdue', v: d.total_ar ?? '—' },
                        { l: 'Aging Buckets', v: String(arBuckets.length || 0), s: 'Category breakdown' },
                    ],
                    ['Receivables management shows positive discipline.', 'DSO helps track cash conversion efficiency.', 'Aging split improves visibility on collection quality.'],
                    ['Old bucket concentration can elevate collection risk.', 'Delayed settlements can tighten liquidity.', 'Mismatch in bucket profile can hide aging buildup.'],
                    ['Focus follow-ups on 60+ day items.', 'Define collection SLAs by customer tier.', 'Tighten terms for repeat late payers.'],
                    chartCard('Aging Buckets Distribution', 'Visual breakdown of receivables by aging bucket.', barChart(arBuckets.map(function (x) { return { month: x.label, raw_amount: normalizeNumber(x.share_pct) }; }), 'raw_amount', 'month', 5, false))
                    + '<div class="kpi-section-card"><h4>Aging Summary Table</h4><p>Detailed breakdown of receivables by category.</p>'
                    + tbl(arRows, ['Aging Bucket', 'Amount', '% of Total'])
                    + '</div>'
                    + explainCards({ title: 'DSO', cards: [
                        { h: '< 30 Days', b: 'Lower aging concentration indicates healthy collections.' },
                        { h: '30-45 Days', b: 'Moderate delay requires closer monitoring and follow-up.' },
                        { h: '> 45 Days', b: 'Elevated risk zone where active intervention is needed.' }
                    ]})
                );
            }
            case 'ap-leverage': {
                var apBuckets = d.aging_buckets || [];
                var apRows = apBuckets.map(function (x) { return [x.label, x.amount, x.percentage]; });
                return detailPage('AP Leverage Analysis', 'Payables profile and supplier payment timing.',
                    [
                        { l: 'Total Payables', v: d.total_ap ?? '—' },
                        { l: 'Days Payables Outstanding', v: d.dpo_badge ?? 'DPO: N/A' },
                        { l: 'Current Overdue', v: d.total_ap ?? '—' },
                        { l: 'Vendor Categories', v: String(apBuckets.length || 0), s: 'Aging buckets' },
                    ],
                    ['AP structure supports tactical liquidity management.', 'DPO helps gauge payment cycle efficiency.'],
                    ['Too-fast payments can reduce cash flexibility.', 'Late payments may trigger supplier friction or penalties.'],
                    ['Align payment runs with cash conversion cycle.', 'Negotiate term improvements with key vendors.'],
                    chartCard('AP Aging Distribution', 'Visual breakdown of payables by aging bucket.', barChart(apBuckets.map(function (x) { return { month: x.label, raw_amount: normalizeNumber(x.share_pct) }; }), 'raw_amount', 'month', 5, false))
                    + '<div class="kpi-section-card"><h4>Aging Summary Table</h4><p>Detailed breakdown of payables by category.</p>'
                    + tbl(apRows, ['Aging Bucket', 'Amount', '% of Total'])
                    + '</div>'
                    + explainCards({ title: 'DPO', cards: [
                        { h: 'Days Payables Outstanding', b: 'Average days the business takes to pay suppliers.' },
                        { h: 'Optimal DPO', b: 'Balance working-capital flexibility with vendor relationship health.' }
                    ]})
                );
            }
            case 'revenue-concentration': {
                var c = d.customers || [];
                var cRows = c.map(function (x, i) { return ['Rank ' + (i + 1), x.name, x.revenue, x.percentage]; });
                return detailPage('Revenue Concentration Analysis', 'Customer concentration and dependency exposure.',
                    [
                        { l: 'Total Revenue (LTM)', v: c.reduce(function (acc, x) { return acc + normalizeNumber(x.revenue); }, 0).toLocaleString() },
                        { l: 'Top 5 Concentration', v: d.top5_percentage ?? '—' },
                        { l: 'Top 5 Revenue', v: c.slice(0, 5).reduce(function (acc, x) { return acc + normalizeNumber(x.revenue); }, 0).toLocaleString() },
                        { l: 'Customer Count', v: String(c.length || 0), s: 'Top customers analyzed' },
                    ],
                    ['Top customer tracking helps forecast retention risk.', 'Concentration metric improves portfolio resilience view.'],
                    ['High concentration elevates single-customer dependency.', 'Revenue shock risk rises when concentration climbs.'],
                    ['Build pipeline targeting diversification goals.', 'Set max-revenue-share threshold per customer.'],
                    chartCard('Top Customers by Revenue', 'Revenue distribution across key customers.', barChart(c.map(function (x) { return { month: x.name, raw_amount: normalizeNumber(x.share_pct) }; }), 'raw_amount', 'month', 6, false))
                    + '<div class="kpi-section-card"><h4>Customer Revenue Breakdown</h4><p>Detailed revenue split by customer.</p>'
                    + tbl(cRows, ['Rank', 'Customer', 'Revenue', '% of Total'])
                    + '</div>'
                    + explainCards({ title: 'Revenue Concentration', cards: [
                        { h: '< 25%', b: 'Low concentration indicates diversified customer mix.' },
                        { h: '25-50%', b: 'Moderate concentration requires monitoring and balancing.' },
                        { h: '> 50%', b: 'High concentration indicates dependency risk on few customers.' }
                    ]})
                );
            }
            default:
                return '<p class="kpi-detail-fallback">No extended layout for <strong>' + esc(kpiType) + '</strong>. Use <em>Open full page</em> if available.</p>';
        }
    };
})(window);
</script>
