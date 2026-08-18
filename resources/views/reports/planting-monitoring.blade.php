<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting &amp; Monitoring Report</title>
    <style>
        @page { margin: 30px 40px; }
        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
        }

        /* --- HEADER --- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%); /* subtle teal gradient */
        }
        .header-table td {
            vertical-align: middle;
        }
        .seal-td {
            width: 70px;
        }
        .seal-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .agency-td {
            text-align: left;
        }
        .agency-republic {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .agency-name {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.2;
        }
        .agency-office {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-top: 2px;
            line-height: 1.2;
        }
        .title-td {
            text-align: right;
            vertical-align: bottom;
            width: 220px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .doc-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        /* --- META BAR --- */
        .meta-bar {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-left: 4px solid #059669;
            padding: 8px 12px;
            margin-bottom: 20px;
            font-size: 9px;
            color: #475569;
        }
        .meta-bar strong { color: #1e293b; font-weight: bold; }
        /* --- KPI WIDGETS --- */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 20px -8px;
            table-layout: fixed;
        }
        .kpi-table td {
            width: 16.66%;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #059669;
            border-radius: 6px;
            padding: 12px 5px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }
        .kpi-table td.died { border-top-color: #ef4444; }
        .kpi-table td.replanted { border-top-color: #eab308; }
        .kpi-val { display: block; font-size: 16px; font-weight: bold; color: #0f172a; margin-bottom: 4px; }
        .kpi-lbl { display: block; font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        /* --- SURVIVAL PANEL --- */
        .survival-td {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 18px 10px;
            text-align: center;
            width: 32%;
        }
        .survival-td .lbl { font-size: 9px; color: #047857; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .survival-td .val { font-size: 28px; font-weight: bold; color: #064e3b; margin: 12px 0; line-height: 1; }
        .survival-badge { display: inline-block; padding: 5px 14px; font-size: 9px; font-weight: bold; text-transform: uppercase; border-radius: 12px; color: #ffffff; }
        .survival-badge.good { background-color: #059669; }
        .survival-badge.attention { background-color: #d97706; }
        .survival-badge.critical { background-color: #dc2626; }
        .survival-td .hint { font-size: 7px; color: #047857; margin-top: 14px; opacity: 0.8; line-height: 1.4; }
        /* --- PERFORMANCE GRID --- */
        .perf-td {
            width: 68%;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0;
        }
        .perf-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .perf-grid td {
            width: 50%;
            padding: 15px 18px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .perf-grid .lbl { display: block; font-size: 8.5px; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px; }
        .perf-grid .val { display: block; font-size: 15px; font-weight: bold; color: #1e293b; }
        /* --- DATATABLES --- */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9px; }
        table.data-table th, table.data-table td { padding: 9px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; text-align: center; }
        table.data-table th.left, table.data-table td.left { text-align: left; }
        table.data-table thead th { background-color: #f8fafc; color: #475569; text-transform: uppercase; font-size: 8px; font-weight: bold; letter-spacing: 0.5px; border-top: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; }
        table.data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        table.data-table tbody tr.total-row { background-color: #ecfdf5; }
        table.data-table tbody tr.total-row td { font-weight: bold; color: #064e3b; border-top: 2px solid #059669; border-bottom: 2px solid #059669; }
        /* --- NOTES & FINDINGS --- */
        ul.findings { margin: 0 0 15px 0; padding-left: 20px; }
        ul.findings li { margin-bottom: 5px; font-size: 9.5px; color: #1e293b; }
        .notes-box { background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; margin-top: 20px; min-height: 60px; }
        .notes-box .lbl { font-size: 10px; font-weight: bold; color: #064e3b; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        .notes-line { border-bottom: 1px dotted #94a3b8; height: 18px; margin-bottom: 4px; }
        /* --- SIGNATURES --- */
        .signatures { width: 100%; border-collapse: collapse; margin-top: 50px; page-break-inside: avoid; }
        .signatures td { width: 33.33%; text-align: center; padding: 0 20px; vertical-align: top; }
        .sig-container { position: relative; }
        .sig-role { font-size: 9px; color: #064e3b; font-weight: bold; text-transform: uppercase; text-align: left; margin-bottom: 30px; letter-spacing: 0.5px; }
        .sig-line { border-top: 1px solid #1e293b; padding-top: 6px; }
        .sig-name { font-size: 10px; color: #64748b; text-transform: uppercase; }
        /* --- UTILS --- */
        .page-break { page-break-after: always; }
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .footer-table td { font-size: 8px; color: #94a3b8; }
        .footer-table td.right { text-align: right; }
        body { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }

        .header-table td {
            vertical-align: middle;
        }
        .seal-td {
            width: 70px;
        }
        .seal-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .agency-td {
            text-align: left;
        }
        .agency-republic {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .agency-name {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            line-height: 1.2;
        }
        .agency-office {
            font-size: 14px;
            font-weight: bold;
            color: #059669;
            margin-top: 2px;
            line-height: 1.2;
        }
        .title-td {
            text-align: right;
            vertical-align: bottom;
            width: 220px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .doc-subtitle {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* --- META BAR --- */
        .meta-bar {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #059669;
            padding: 8px 12px;
            margin-bottom: 20px;
            font-size: 9px;
            color: #475569;
        }
        .meta-bar strong { color: #1e293b; font-weight: bold; }

        /* --- SECTION HEADERS --- */
        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 20px;
            margin-bottom: 12px;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 4px;
        }

        /* --- KPI WIDGETS --- */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 20px -8px;
        }
        .kpi-table td {
            width: 16.66%;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #059669;
            border-radius: 4px;
            padding: 10px 5px;
            text-align: center;
        }
        .kpi-table td.died { border-top-color: #ef4444; }
        .kpi-table td.replanted { border-top-color: #eab308; }
        .kpi-val {
            display: block;
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .kpi-lbl {
            display: block;
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* --- HIGHLIGHT PANELS --- */
        .highlight-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin: 0 -12px 20px -12px;
        }
        .highlight-table > tbody > tr > td {
            vertical-align: top;
            padding: 0;
        }
        .survival-panel {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 18px 15px;
            text-align: center;
            width: 35%;
        }
        .survival-panel .lbl {
            font-size: 9px;
            color: #047857;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .survival-panel .val {
            font-size: 40px;
            font-weight: bold;
            color: #064e3b;
            margin: 10px 0;
            line-height: 1;
        }
        .survival-badge {
            display: inline-block;
            padding: 5px 14px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 12px;
            color: #ffffff;
        }
        .survival-badge.good { background-color: #059669; }
        .survival-badge.attention { background-color: #d97706; }
        .survival-badge.critical { background-color: #dc2626; }
        .survival-panel .hint {
            font-size: 7.5px;
            color: #047857;
            margin-top: 12px;
            opacity: 0.8;
        }

        .perf-panel {
            width: 65%;
        }
        .perf-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .perf-grid td {
            width: 50%;
            padding: 14px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .perf-grid td.rounded-tl { border-top-left-radius: 6px; }
        .perf-grid td.rounded-tr { border-top-right-radius: 6px; }
        .perf-grid td.rounded-bl { border-bottom-left-radius: 6px; }
        .perf-grid td.rounded-br { border-bottom-right-radius: 6px; }
        .perf-grid .lbl {
            display: block;
            font-size: 9px;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .perf-grid .val {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }

        /* --- DATATABLES --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        table.data-table th, table.data-table td {
            padding: 9px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: center;
        }
        table.data-table th.left, table.data-table td.left {
            text-align: left;
        }
        table.data-table thead th {
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 2px solid #cbd5e1;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table tbody tr.total-row {
            background-color: #ecfdf5;
        }
        table.data-table tbody tr.total-row td {
            font-weight: bold;
            color: #064e3b;
            border-top: 2px solid #059669;
            border-bottom: 2px solid #059669;
        }

        /* --- NOTES & FINDINGS --- */
        ul.findings {
            margin: 0 0 15px 0;
            padding-left: 20px;
        }
        ul.findings li {
            margin-bottom: 5px;
            font-size: 9.5px;
            color: #1e293b;
        }
        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            min-height: 60px;
        }
        .notes-box .lbl {
            font-size: 10px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .notes-line {
            border-bottom: 1px dotted #94a3b8;
            height: 18px;
            margin-bottom: 4px;
        }

        /* --- SIGNATURES --- */
        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            padding: 0 20px;
            vertical-align: top;
        }
        .sig-container {
            position: relative;
        }
        .sig-role {
            font-size: 9px;
            color: #064e3b;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            margin-bottom: 30px;
            letter-spacing: 0.5px;
        }
        .sig-line {
            border-top: 1px solid #1e293b;
            padding-top: 6px;
        }
        .sig-name {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }

        /* --- UTILS --- */
        .page-break { page-break-after: always; }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .footer-table td {
            font-size: 8px;
            color: #94a3b8;
        }
        .footer-table td.right { text-align: right; }
    </style>
</head>
<body>
    {{-- ========== PAGE 1: Executive Summary ========== --}}
    <div class="page-break">
        @if(!empty($headerDataUri))
            <div style="text-align:center; margin-bottom: 15px; border-bottom: 3px solid #059669; padding-bottom: 12px;">
                <img src="{{ $headerDataUri }}" alt="MENRO Letterhead" style="width:100%; height:auto;">
            </div>
        @else
            <table class="header-table">
                <tr>
                    <td class="seal-td">
                        @if(!empty($menroSealDataUri))
                            <img src="{{ $menroSealDataUri }}" alt="MENRO Seal" class="seal-img">
                        @elseif(!empty($provinceSealDataUri))
                            <img src="{{ $provinceSealDataUri }}" alt="Province Seal" class="seal-img">
                        @endif
                    </td>
                    <td class="agency-td">
                        <div class="agency-republic">Republic of the Philippines</div>
                        <div class="agency-name">PROVINCE OF MISAMIS ORIENTAL</div>
                        <div class="agency-name">MUNICIPALITY OF TAGOLOAN</div>
                        <div class="agency-office">Municipal Environment and Natural Resources Office</div>
                    </td>
                    <td class="title-td">
                        <div class="doc-title">Planting &amp;<br>Monitoring Report</div>
                        <div class="doc-subtitle">Environmental Impact Summary</div>
                    </td>
                </tr>
            </table>
        @endif

        <div class="meta-bar">
            Generated: <strong>{{ $generatedAt->format('M d, Y g:i A') }}</strong>
            @if(!empty($filterNote))
                &nbsp;|&nbsp; Filters: <strong>{{ $filterNote }}</strong>
            @endif
        </div>

        <div class="section-header">Key Performance Indicators</div>
        <table class="kpi-table">
            <tr>
                <td>
                    <span class="kpi-val">{{ number_format($summary['record_count']) }}</span>
                    <span class="kpi-lbl">Monitoring Records</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['seedlings_planted']) }}</span>
                    <span class="kpi-lbl">Seedlings Planted</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['survived_count']) }}</span>
                    <span class="kpi-lbl">Survived</span>
                </td>
                <td class="died">
                    <span class="kpi-val">{{ number_format($totals['died_count']) }}</span>
                    <span class="kpi-lbl">Died</span>
                </td>
                <td class="replanted">
                    <span class="kpi-val">{{ number_format($totals['replanted_count']) }}</span>
                    <span class="kpi-lbl">Re-planted</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['survival_rate'], 2) }}%</span>
                    <span class="kpi-lbl">Survival Rate</span>
                </td>
            </tr>
        </table>

        <div class="section-header">Survival Rate &amp; Monitoring Performance</div>
        <table class="highlight-table">
            <tr>
                <td class="survival-td">
                    <div class="lbl">Overall Survival</div>
                    <div class="val">{{ number_format($totals['survival_rate'], 2) }}%</div>
                    <span class="survival-badge {{ $summary['survival_band'] === 'good' ? 'good' : ($summary['survival_band'] === 'attention' ? 'attention' : 'critical') }}">
                        {{ $summary['survival_band_label'] }}
                    </span>
                    <div class="hint">Excellent &gt;= 85%<br>Good &gt;= 70% &nbsp;|&nbsp; Attention &lt; 70%</div>
                </td>
                <td class="perf-td">
                    <table class="perf-grid">
                        <tr>
                            <td class="no-border-top no-border-left">
                                <span class="lbl">Monitoring Events</span>
                                <span class="val">{{ number_format($summary['record_count']) }}</span>
                            </td>
                            <td class="no-border-top no-border-right">
                                <span class="lbl">Agencies / Requesters</span>
                                <span class="val">{{ number_format($summary['agency_count']) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="no-border-bottom no-border-left">
                                <span class="lbl">Distinct Sites / Areas</span>
                                <span class="val">{{ number_format($summary['site_count']) }}</span>
                            </td>
                            <td class="no-border-bottom no-border-right">
                                <span class="lbl">Coverage Period</span>
                                <span class="val">
                                    @if($summary['date_from'] && $summary['date_to'])
                                        {{ $summary['date_from']->format('M d, Y') }} - {{ $summary['date_to']->format('M d, Y') }}
                                    @else
                                        Overall Summary
                                    @endif
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="section-header">Key Findings</div>
        <ul class="findings">
            @forelse($summary['findings'] as $finding)
                <li>{{ $finding }}</li>
            @empty
                <li>No significant findings identified for the current filters.</li>
            @endforelse
        </ul>

        <div class="section-header">Geographic / Area Summary</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="left" style="width:36%">Area / Barangay</th>
                    <th style="width:12%">Records</th>
                    <th style="width:16%">Planted</th>
                    <th style="width:16%">Survived</th>
                    <th style="width:10%">Died</th>
                    <th style="width:10%">Survival %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary['areas'] as $area)
                    <tr>
                        <td class="left">{{ $area['area'] }}</td>
                        <td>{{ $area['records'] }}</td>
                        <td>{{ number_format($area['seedlings_planted']) }}</td>
                        <td>{{ number_format($area['survived_count']) }}</td>
                        <td>{{ number_format($area['died_count']) }}</td>
                        <td>
                            <span style="color: {{ $area['survival_rate'] >= 70 ? '#059669' : '#dc2626' }}; font-weight: bold;">
                                {{ number_format($area['survival_rate'], 2) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No geographic data available for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <table class="footer-table">
            <tr>
                <td class="left">MENRO Tagoloan | Official Document</td>
                <td class="right">Page 1 of 2</td>
            </tr>
        </table>
    </div>

    {{-- ========== PAGE 2: Detailed Records ========== --}}
    <div>
        <table class="header-table" style="margin-bottom: 20px;">
            <tr>
                <td class="agency-td">
                    <div class="agency-name">MENRO TAGOLOAN</div>
                    <div class="agency-republic">Planting &amp; Monitoring Detailed Records</div>
                </td>
                <td class="title-td">
                    <div class="doc-subtitle">Generated: {{ $generatedAt->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <colgroup>
                <col style="width: 8%">
                <col style="width: 18%">
                <col style="width: 16%">
                <col style="width: 14%">
                <col style="width: 8%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 8%">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2" class="left">Date Planted</th>
                    <th rowspan="2" class="left">Agency / Requester</th>
                    <th rowspan="2" class="left">Area Planted</th>
                    <th rowspan="2" class="left">Seedling Type</th>
                    <th colspan="6" style="border-bottom: 1px solid #cbd5e1;">Monitoring Data</th>
                </tr>
                <tr>
                    <th>Date</th>
                    <th>Planted</th>
                    <th>Replant</th>
                    <th>Survived</th>
                    <th>Died</th>
                    <th>Survival %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td class="left">{{ optional($record->request?->request_date)->format('m/d/Y') }}</td>
                        <td class="left">{{ $record->request?->agency?->name ?? $record->request?->requester_name ?? '-' }}</td>
                        <td class="left">{{ $record->request?->location }}</td>
                        <td class="left">{{ $record->seedling_type }}</td>
                        <td>{{ optional($record->date_monitoring)->format('m/d/Y') }}</td>
                        <td>{{ $record->seedlings_planted }}</td>
                        <td>{{ $record->replanted_count }}</td>
                        <td>{{ $record->survived_count }}</td>
                        <td>{{ $record->died_count }}</td>
                        <td>
                            @php
                                $rate = $record->seedlings_planted > 0 ? ($record->survived_count / $record->seedlings_planted * 100) : 0;
                            @endphp
                            <span style="color: {{ $rate >= 70 ? '#059669' : '#dc2626' }}; font-weight: bold;">
                                {{ number_format($rate, 2) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">No monitoring records match the selected filters.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="5" class="left">OVERALL TOTALS</td>
                    <td>{{ number_format($totals['seedlings_planted']) }}</td>
                    <td>{{ number_format($totals['replanted_count']) }}</td>
                    <td>{{ number_format($totals['survived_count']) }}</td>
                    <td>{{ number_format($totals['died_count']) }}</td>
                    <td>{{ number_format($totals['survival_rate'], 2) }}%</td>
                </tr>
            </tbody>
        </table>

        <div class="notes-box">
            <div class="lbl">Additional Notes &amp; Observations</div>
            <div class="notes-line"></div>
            <div class="notes-line"></div>
            <div class="notes-line"></div>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sig-container">
                        <div class="sig-role">Prepared By</div>
                        <div class="sig-line">
                            <div class="sig-name">Signature over Printed Name</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="sig-container">
                        <div class="sig-role">Reviewed By</div>
                        <div class="sig-line">
                            <div class="sig-name">Signature over Printed Name</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="sig-container">
                        <div class="sig-role">Approved By</div>
                        <div class="sig-line">
                            <div class="sig-name">Signature over Printed Name</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="footer-table">
            <tr>
                <td class="left">MENRO Tagoloan | Official Document</td>
                <td class="right">Page 2 of 2</td>
            </tr>
        </table>
    </div>
</body>
</html>
