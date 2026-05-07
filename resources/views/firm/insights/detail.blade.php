@extends('layouts.app')
@section('page-title', 'Insight Report')

@push('styles')
<style>
.report-detail-container { background-color:#f5f7fa;min-height:100vh; }
.report-detail-header { display:flex;justify-content:space-between;align-items:flex-start;background:white;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.08); }
.report-detail-header h1 { font-size:22px;font-weight:700;color:#1a202c;margin:0 0 6px; }
.report-meta { display:flex;flex-wrap:wrap;gap:20px;margin-top:10px; }
.report-meta-item { display:flex;align-items:center;gap:8px;color:#64748b;font-size:14px; }
.report-meta-item i { color:#94a3b8;font-size:15px; }
.report-meta-item.report-meta-files i { color:#eab308; }
.export-pdf-btn { color:white;border:none;padding:12px 20px;font-size:14px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:10px;background:#F06B25;border-radius:5px;text-decoration:none;white-space:nowrap;flex-shrink:0;box-shadow:0 5px 10px 0 rgba(240,107,37,.2);transition:all .3s ease; }
.export-pdf-btn:hover { background:#d95a1a;color:white;text-decoration:none;transform:translateY(-1px); }
.btn-back { background:#fff;color:#0788BE;padding:12px 20px;border-radius:5px;border:1px solid #0788be;text-decoration:none;display:inline-flex;align-items:center;gap:8px;margin-bottom:20px;font-weight:500;transition:all .2s;box-shadow:0 5px 10px 0 rgba(16,134,190,.2); }
.btn-back:hover { background:#f0f9ff;color:#1559a6;text-decoration:none; }
.insight-slider-container { position:relative;background:#f5f7fa;border-radius:12px;min-height:500px;margin-top:0; }
.insight-slider-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;margin-top:4px;padding:0 4px; }
.insight-slider-counter { color:#64748b;font-size:15px;font-weight:500; }
.insight-slider-nav { display:flex;align-items:center;gap:20px; }
.insight-slider-nav-section-btn-container { display:flex;align-items:center;gap:10px;border:1px solid #DBDBDB;border-radius:30px;padding:5px;cursor:pointer;transition:all .3s ease;background:none;font-family:inherit;outline:none; }
.insight-slider-nav-section-btn-container:hover { border-color:#EF6A24; }
.insight-slider-nav-btn { width:40px;height:40px;border-radius:50%;background:linear-gradient(124.07deg,#EF6A24 19.79%,#F99D3C 88.53%);color:white;border:none;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;pointer-events:none; }
.insight-slider-nav-section-btn { font-size:14px;color:#474747;padding:0 10px 0 0;font-weight:400; }
.insight-slider-nav-section-btn.last-child-btn { padding:0 0 0 10px; }
.insight-slider-nav-section-btn:disabled { color:#94a3b8;cursor:not-allowed; }
.insight-slider-slides { position:relative;overflow:hidden;min-height:400px; }
.insight-slider-slide { display:none;animation:fadeInSlide .4s ease; }
.insight-slider-slide.active { display:block; }
@keyframes fadeInSlide { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }
.insight-slider-card { background:white;border-radius:12px;padding:28px 32px;box-shadow:0 2px 8px rgba(0,0,0,.08); }
.insight-slider-title { display:flex;align-items:center;gap:16px;margin-bottom:24px; }
.insight-slider-title-icon { width:44px;height:44px;background:#F06B25;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;flex-shrink:0; }
.insight-slider-title h2 { margin:0;font-size:24px;font-weight:600;color:#1a202c; }
.insight-slider-content { color:#2d3748;font-size:15px;line-height:1.8; }
.insight-slider-content ul,.insight-slider-content ol { padding-left:20px;margin:0; }
.insight-slider-content li { margin:8px 0;list-style-type:circle; }
.insight-slider-content strong { color:#1559a6;font-weight:600; }
.insight-slider-content p { margin:0 0 10px; }
.insight-slider-content h3,.insight-slider-content h4 { color:#1559a6;margin-top:10px;margin-bottom:0;font-weight:600; }
.insight-slider-content table.insight-table { width:100%;border-collapse:collapse;margin:0;box-shadow:0 1px 3px rgba(0,0,0,.1);border-radius:8px;overflow:hidden; }
.insight-slider-content table.insight-table th { border:1px solid #e2e8f0;padding:12px;text-align:left;font-weight:600;color:#2d3748;background:#f7fafc; }
.insight-slider-content table.insight-table td { border:1px solid #e2e8f0;padding:12px;color:#4a5568; }
.insight-slider-content table.insight-table tbody tr:nth-child(even) { background:#f9fafb; }
.insight-slider-pagination { display:flex;justify-content:center;gap:8px;margin-top:24px; }
.insight-slider-dot { width:10px;height:10px;border-radius:50%;background:#cbd5e0;cursor:pointer;transition:all .3s ease; }
.insight-slider-dot.active { background:#0788be;width:24px;border-radius:5px; }
.insight-slider-dot:hover { background:#94a3b8; }
@media print {
    .btn-back,.export-pdf-btn,.insight-slider-nav,.insight-slider-pagination { display:none !important; }
    .insight-slider-slide { display:block !important;page-break-after:always; }
    .insight-slider-card { box-shadow:none;border:1px solid #e2e8f0; }
}
</style>
@endpush

@section('content')
<div class="report-detail-container">

    <a href="{{ route('firm-insights.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Go Back
    </a>

    <div class="report-detail-header">
        <div>
            <h1>{{ $report->report_name }}</h1>
            <div class="report-meta">
                <div class="report-meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ $report->created_at->format('M d, Y') }}</span>
                </div>
                <div class="report-meta-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>{{ $report->formatted_file_size }}</span>
                </div>
                <div class="report-meta-item report-meta-files">
                    <i class="fas fa-folder"></i>
                    <span>{{ $report->attachments->count() }} file(s)</span>
                </div>
            </div>
        </div>
        @if($report->insight_json_data)
        <a href="{{ route('firm-insights.export-pdf', $report->id) }}"
           class="export-pdf-btn"
           onclick="var btn=this;btn.querySelector('span').textContent='Generating PDF';btn.querySelector('i').className='fas fa-spinner fa-spin';btn.style.pointerEvents='none';btn.style.opacity='0.85';setTimeout(function(){btn.querySelector('span').textContent='Export to PDF';btn.querySelector('i').className='fas fa-file-pdf';btn.style.pointerEvents='';btn.style.opacity='';},8000);">
            <span>Export to PDF</span>
            <i class="fas fa-file-pdf"></i>
        </a>
        @endif
    </div>

    @if($report->insight_json_data)
        <div id="insightAccordionContainer">
            <div id="insightLoaderShow" style="display:none;"></div>
            <div id="insightAccordionContentShow" style="display:none;"></div>
        </div>
    @else
        <div style="text-align:center;padding:60px;background:white;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
            <p style="color:#718096;font-size:18px;">
                @if(strtolower($report->status) === 'failed')
                    Failed to generate insights for this report.
                @else
                    No insights available yet. Please check back later.
                @endif
            </p>
        </div>
    @endif

</div>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endsection

@push('scripts')
<script>
(function(){
    if (!document.getElementById('insightLoaderShow')) return;

    if (window.showAppLoading) window.showAppLoading('Loading insights', 'Preparing your report insights...');
    fetch('/firm-insights/{{ $report->id }}/data', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(response => {
        if (window.hideAppLoading) window.hideAppLoading();
        if (response.status === 'success' && response.data) {
            displayInsightSlider(response.data);
        } else {
            document.getElementById('insightLoaderShow').style.display = 'none';
            document.getElementById('insightAccordionContentShow').innerHTML =
                '<div style="padding:40px;text-align:center;color:#718096;font-size:16px;">No insight data available.</div>';
            document.getElementById('insightAccordionContentShow').style.display = '';
        }
    })
    .catch(() => {
        if (window.hideAppLoading) window.hideAppLoading();
        document.getElementById('insightLoaderShow').style.display = 'none';
        document.getElementById('insightAccordionContentShow').innerHTML =
            '<div style="padding:40px;text-align:center;color:#e53e3e;font-size:16px;">Failed to load insights. Please refresh the page.</div>';
        document.getElementById('insightAccordionContentShow').style.display = '';
    });

    function displayInsightSlider(data) {
        document.getElementById('insightLoaderShow').style.display = 'none';
        let reportText = '';
        if (typeof data === 'string') { reportText = data; }
        else if (data && data.report && typeof data.report === 'string') { reportText = data.report; }
        else if (data && data.insight_json_data && data.insight_json_data.report) { reportText = data.insight_json_data.report; }
        else if (data && typeof data === 'object') {
            let longest = '';
            for (let key in data) { if (typeof data[key] === 'string' && data[key].length > longest.length) longest = data[key]; }
            reportText = longest;
        }
        if (!reportText) {
            document.getElementById('insightAccordionContentShow').innerHTML =
                '<div style="padding:40px;text-align:center;color:#718096;font-size:16px;background:white;border-radius:12px;">No report content found.</div>';
            document.getElementById('insightAccordionContentShow').style.display = '';
            return;
        }
        const sections = parseInsightSections(reportText);
        let html = '<div class="insight-slider-container"><div class="insight-slider-wrapper">';
        html += '<div class="insight-slider-header">';
        html += '<div class="insight-slider-counter"><span id="slider-current">1</span> of <span id="slider-total">' + sections.length + '</span></div>';
        html += '<div class="insight-slider-nav">';
        html += '<button type="button" class="insight-slider-nav-section-btn-container" id="slider-prev-container" onclick="changeSlide(-1)" aria-label="Previous"><span class="insight-slider-nav-btn" id="slider-prev"><i class="fas fa-chevron-left"></i></span><p class="insight-slider-nav-section-btn" id="slider-prev-label">—</p></button>';
        html += '<button type="button" class="insight-slider-nav-section-btn-container" id="slider-next-container" onclick="changeSlide(1)" aria-label="Next"><p class="insight-slider-nav-section-btn last-child-btn" id="slider-next-label">—</p><span class="insight-slider-nav-btn" id="slider-next"><i class="fas fa-chevron-right"></i></span></button>';
        html += '</div></div><div class="insight-slider-slides">';
        sections.forEach((section, i) => {
            html += `<div class="insight-slider-slide ${i===0?'active':''}" data-slide-index="${i}"><div class="insight-slider-card"><div class="insight-slider-title"><div class="insight-slider-title-icon"><i class="fas fa-${getSectionIcon(section.title)}"></i></div><h2>${section.title}</h2></div><div class="insight-slider-content">${section.content}</div></div></div>`;
        });
        html += '</div><div class="insight-slider-pagination">';
        sections.forEach((_,i) => { html += `<div class="insight-slider-dot ${i===0?'active':''}" onclick="goToSlide(${i})"></div>`; });
        html += '</div></div></div>';
        window.insightSections = sections; window.currentSlide = 0;
        document.getElementById('insightAccordionContentShow').innerHTML = html;
        document.getElementById('insightAccordionContentShow').style.display = '';
        setTimeout(() => goToSlide(0), 100);
    }

    const canonicalSections = ['Executive Summary','Financial Snapshot','Key Trends & Drivers','Wins & Opportunities','Risks & Concerns','Forensic Observations','Actionable Recommendations'];
    function normalizeTitle(t) { return t.replace(/\s+/g,' ').trim().toLowerCase(); }

    function parseInsightSections(reportText) {
        let sections = [];
        const escaped = canonicalSections.map(t => t.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'));
        const re = new RegExp('(?:^|\\n)('+escaped.join('|')+')\\s*\\n','g');
        const matches = []; let m;
        while ((m = re.exec(reportText)) !== null) matches.push({title:m[1],contentStart:m.index+m[0].length,matchStart:m.index});
        if (matches.length > 0) {
            matches.forEach((meta,i) => {
                const end = i < matches.length-1 ? matches[i+1].matchStart : reportText.length;
                sections.push({title:meta.title,content:formatContent(reportText.substring(meta.contentStart,end).trim())});
            });
            if (sections.length > 0) return sections;
        }
        const parts = reportText.split(/\n\s*[═─=]{10,}\s*\n/);
        if (parts.length > 1) {
            const map = {}; const n2c = {};
            canonicalSections.forEach(c => n2c[normalizeTitle(c)] = c);
            parts.forEach(part => {
                part = part.trim(); if (!part) return;
                const nl = part.indexOf('\n');
                const title = nl !== -1 ? part.substring(0,nl).trim() : part;
                const body  = nl !== -1 ? part.substring(nl+1).trim() : '';
                if (title.length > 2) {
                    const exp = expandByCanonical(title, body, n2c);
                    Object.keys(exp).forEach(t => { map[normalizeTitle(t)] = {title:t,content:formatContent(exp[t])}; });
                }
            });
            if (Object.keys(map).length > 0) {
                canonicalSections.forEach(c => { const k = normalizeTitle(c); if (map[k]) sections.push(map[k]); });
                if (sections.length > 0) return sections;
            }
        }
        const mdPat = /^##\s+(?:\d+\.\s*)?(.+)$/gm; let mdM; const mdList = [];
        while ((mdM = mdPat.exec(reportText)) !== null) mdList.push({title:mdM[1].trim(),startIdx:mdM.index,endIdx:mdM.index+mdM[0].length});
        if (mdList.length > 0) {
            const list = [];
            mdList.forEach((mm,i) => {
                const end = i < mdList.length-1 ? mdList[i+1].startIdx : reportText.length;
                const content = reportText.substring(mm.endIdx,end).trim();
                const cleanTitle = mm.title.replace(/^\d+\.\s*/,'').trim();
                if (cleanTitle.length > 2 && content) list.push({title:cleanTitle,content:formatContent(content)});
            });
            const byNorm = {};
            list.forEach(s => byNorm[normalizeTitle(s.title)] = s);
            canonicalSections.forEach(c => { const k = normalizeTitle(c); if (byNorm[k]) sections.push(byNorm[k]); });
            list.forEach(s => { if (!canonicalSections.some(c => normalizeTitle(c)===normalizeTitle(s.title))) sections.push(s); });
            if (sections.length > 0) return sections;
        }
        return [{title:'Financial Insights',content:formatContent(reportText)}];
    }

    function expandByCanonical(partTitle, partContent, n2c) {
        const result = {}; const lines = partContent.split('\n');
        let cur = partTitle, buf = [];
        lines.forEach(line => {
            const ln = normalizeTitle(line.trim());
            if (line.trim() !== '' && n2c.hasOwnProperty(ln)) { if (cur !== '') result[cur] = buf.join('\n').trim(); cur = n2c[ln]; buf = []; }
            else buf.push(line);
        });
        if (cur !== '') result[cur] = buf.join('\n').trim();
        return result;
    }

    function formatContent(content) {
        if (!content) return '';
        content = content.replace(/^[\s\-_*]{3,}$/gm,'').replace(/^[\s═─=]{3,}$/gm,'').replace(/\n{3,}/g,'\n\n').trim();
        content = convertMarkdownTable(content);
        content = content.replace(/^###\s+(.+)$/gm,'<h3>$1</h3>').replace(/^####\s+(.+)$/gm,'<h4>$1</h4>');
        content = content.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/(?<!\*)\*([^*]+?)\*(?!\*)/g,'<em>$1</em>');
        const lines = content.split('\n'); let inList=false, listType=null, listItems=[], processed=[];
        lines.forEach(line => {
            const t = line.trim();
            if (/^[\-\*_]{3,}$/.test(t) || /^[═─=]{3,}$/.test(t)) return;
            if (t.startsWith('<') && /<(table|thead|tbody|tr|th|td|h[1-6]|ul|ol|\/ul|\/ol)/.test(t)) {
                if (inList && listItems.length) { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; inList=false; }
                processed.push(line); return;
            }
            const ul = t.match(/^[\-\*\+]\s+(.+)$/); const ol = t.match(/^\d+\.\s+(.+)$/);
            if (ul) { if (inList && listType!=='ul') { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; } inList=true; listType='ul'; listItems.push(`<li>${ul[1]}</li>`); }
            else if (ol && !t.match(/^[A-Z\s:]+$/)) { if (inList && listType!=='ol') { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; } inList=true; listType='ol'; listItems.push(`<li>${ol[1]}</li>`); }
            else { if (inList && listItems.length) { processed.push(`<${listType}>${listItems.join('')}</${listType}>`); listItems=[]; inList=false; } processed.push(line); }
        });
        if (inList && listItems.length) processed.push(`<${listType}>${listItems.join('')}</${listType}>`);
        content = processed.join('\n');
        let fmt = '';
        content.split(/\n\n+/).forEach(para => {
            para = para.trim(); if (!para) return;
            if (para.match(/^<(ul|ol|table|h[1-6])/i)) fmt += para+'\n';
            else fmt += '<p>'+para.replace(/\n/g,'<br>')+'</p>\n';
        });
        if (!fmt) fmt = '<p>'+content.replace(/\n/g,'<br>')+'</p>';
        return fmt.replace(/<p>[\s\-_*═─=]{3,}<\/p>/gi,'').replace(/<p>\s*<\/p>/g,'');
    }

    function convertMarkdownTable(text) {
        return text.replace(/(\|.+\|\n\|[\s\-\|:]+\|\n(?:\|.+\|\n?)+)/g, match => {
            const lines = match.trim().split('\n'); if (lines.length < 2) return match;
            let html = '<div class="table-responsive"><table class="insight-table"><thead><tr>';
            lines[0].split('|').map(c=>c.trim()).filter(c=>c).forEach(c => html+=`<th>${c}</th>`);
            html += '</tr></thead><tbody>';
            for (let i=2;i<lines.length;i++) {
                const row = lines[i]; if (!row.trim()) continue;
                const cells = row.split('|').map(c=>c.trim()).filter(c=>c); if (!cells.length) continue;
                html += '<tr>';
                cells.forEach(cell => { html += `<td style="text-align:${/^[\$0-9,.\-%\s]+$/.test(cell)?'right':'left'};">${cell}</td>`; });
                html += '</tr>';
            }
            return html+'</tbody></table></div>';
        });
    }

    window.changeSlide = function(dir) {
        if (!window.insightSections) return;
        const total = window.insightSections.length;
        let next = window.currentSlide + dir;
        if (next < 0) next = total-1; else if (next >= total) next = 0;
        goToSlide(next);
    };

    window.goToSlide = function(index) {
        if (!window.insightSections) return;
        const total = window.insightSections.length;
        if (index < 0 || index >= total) return;
        window.currentSlide = index;
        document.querySelectorAll('.insight-slider-slide').forEach(s => s.classList.remove('active'));
        const slide = document.querySelector(`.insight-slider-slide[data-slide-index="${index}"]`);
        if (slide) slide.classList.add('active');
        document.querySelectorAll('.insight-slider-dot').forEach((d,i) => d.classList.toggle('active', i===index));
        const cur = document.getElementById('slider-current'); if (cur) cur.textContent = index+1;
        const pc = document.getElementById('slider-prev-container'), nc = document.getElementById('slider-next-container');
        const pl = document.getElementById('slider-prev-label'), nl = document.getElementById('slider-next-label');
        if (pc) pc.style.display = index===0?'none':'flex';
        if (nc) nc.style.display = index===total-1?'none':'flex';
        if (total<=1 && pc && nc) { pc.style.display='none'; nc.style.display='none'; }
        if (pl) { pl.textContent = index>0 ? window.insightSections[index-1].title : '—'; pl.style.display = index===0?'none':''; }
        if (nl) { nl.textContent = index<total-1 ? window.insightSections[index+1].title : '—'; nl.style.display = index===total-1?'none':''; }
    };

    document.addEventListener('keydown', function(e) {
        if (document.querySelector('.insight-slider-container')) {
            if (e.key==='ArrowLeft') { e.preventDefault(); changeSlide(-1); }
            else if (e.key==='ArrowRight') { e.preventDefault(); changeSlide(1); }
        }
    });

    function getSectionIcon(title) {
        const t = title.toLowerCase();
        if (t.includes('executive')&&t.includes('summary')) return 'file-invoice';
        if (t.includes('financial')&&t.includes('snapshot')) return 'chart-bar';
        if (t.includes('key')&&t.includes('trend')) return 'chart-line';
        if (t.includes('win')&&t.includes('opportunit')) return 'trophy';
        if (t.includes('risk')&&t.includes('concern')) return 'shield-alt';
        if (t.includes('forensic')) return 'search';
        if (t.includes('actionable')&&t.includes('recommendation')) return 'lightbulb';
        if (t.includes('cash')||t.includes('flow')) return 'money-bill-wave';
        if (t.includes('profit')||t.includes('loss')) return 'balance-scale';
        if (t.includes('revenue')||t.includes('income')) return 'dollar-sign';
        return 'chart-bar';
    }
}());
</script>
@endpush
