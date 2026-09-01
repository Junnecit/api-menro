<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting &amp; Monitoring Report</title>
    <style>
        @page {
            margin: 18px 26px 30px 26px;
            size: legal portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.3;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* --- FIXED BOTTOM FOOTER --- */
        footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 16px;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            font-size: 7.5px;
            color: #64748b;
            vertical-align: middle;
            padding: 0;
        }
        .footer-table td.left {
            text-align: left;
        }
        .footer-table td.right {
            text-align: right;
            padding-right: 65px;
        }

        /* --- OFFICIAL HEADER --- */
        .header-container {
            width: 100%;
            margin-bottom: 6px;
            border-bottom: 2px solid #059669;
            padding-bottom: 4px;
            text-align: center;
        }
        .header-img {
            width: 78%;
            max-width: 500px;
            height: auto;
            display: block;
            margin: 0 auto;
            padding: 0;
        }
        .header-table {
            width: 80%;
            margin: 0 auto 2px auto;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .seal-td {
            width: 48px;
            text-align: center;
        }
        .seal-img {
            width: 42px;
            height: 42px;
        }
        .agency-td {
            text-align: center;
            padding: 0 8px;
        }
        .agency-office-top {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.3px;
            line-height: 1.15;
        }
        .agency-republic {
            font-size: 7.5px;
            color: #475569;
            line-height: 1.15;
            margin-top: 1px;
        }
        .agency-province {
            font-size: 8.5px;
            color: #0f172a;
            font-weight: bold;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.15;
            margin-top: 1px;
        }
        .agency-municipality {
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.15;
            margin-top: 1px;
        }

        /* --- DOCUMENT TITLE & SUBTITLE (CLEAN CENTERED) --- */
        .report-title-section {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 0 0 1px 0;
        }
        .report-subtitle {
            font-size: 7.5px;
            color: #475569;
            margin: 0 0 1px 0;
        }
        .report-meta {
            font-size: 7px;
            color: #64748b;
            margin: 0;
        }

        /* --- SECTION TITLES WITH FULL-WIDTH GREEN DIVIDER --- */
        .section-title {
            font-size: 8px;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-top: 7px;
            margin-bottom: 4px;
            border-bottom: 1.5px solid #059669;
            padding-bottom: 2px;
        }

        /* --- 1. KEY PERFORMANCE INDICATORS (6 CARDS IN A ROW) --- */
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px 0;
            table-layout: fixed;
            margin-bottom: 6px;
        }
        .kpi-grid td {
            width: 16.66%;
            background-color: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-radius: 3px;
            padding: 5px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-val {
            display: block;
            font-size: 11.5px;
            font-weight: bold;
            color: #064e3b;
            line-height: 1.1;
        }
        .kpi-lbl {
            display: block;
            font-size: 6px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* --- 2. SURVIVAL RATE & MONITORING PERFORMANCE PANEL --- */
        .performance-panel {
            width: 100%;
            background-color: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-radius: 3px;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .performance-panel td {
            vertical-align: middle;
            padding: 7px 10px;
        }
        .perf-left {
            width: 32%;
            text-align: center;
            border-right: 1px solid #a7f3d0;
        }
        .perf-mid {
            width: 34%;
            padding-left: 12px !important;
            border-right: 1px solid #e2e8f0;
        }
        .perf-right {
            width: 34%;
            padding-left: 12px !important;
        }
        .perf-stat-group {
            margin-bottom: 4px;
        }
        .perf-stat-group:last-child {
            margin-bottom: 0;
        }
        .perf-stat-lbl {
            font-size: 7px;
            color: #64748b;
            display: block;
        }
        .perf-stat-val {
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
            display: block;
            margin-top: 0.5px;
        }
        .perf-overall-lbl {
            font-size: 7px;
            color: #059669;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .perf-overall-val {
            font-size: 19px;
            font-weight: bold;
            color: #064e3b;
            margin: 2px 0 3px 0;
            line-height: 1;
        }
        .survival-pill {
            display: inline-block;
            background-color: #059669;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 1.5px 8px;
            border-radius: 8px;
            letter-spacing: 0.3px;
        }
        .survival-pill.attention { background-color: #d97706; }
        .survival-pill.critical { background-color: #dc2626; }
        .perf-overall-hint {
            font-size: 5.5px;
            color: #047857;
            margin-top: 3px;
            opacity: 0.85;
        }

        /* --- 3. KEY FINDINGS LIST --- */
        .findings-list {
            margin: 2px 0 6px 0;
            padding-left: 14px;
        }
        .findings-list li {
            font-size: 7.5px;
            color: #1e293b;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        /* --- 4. GEOGRAPHIC / AREA SUMMARY TABLE --- */
        .area-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7.5px;
        }
        .area-summary-table th, .area-summary-table td {
            border: 1px solid #a7f3d0;
            padding: 3.5px 5px;
            vertical-align: middle;
            text-align: center;
        }
        .area-summary-table th {
            background-color: #f0fdf4;
            color: #065f46;
            font-weight: bold;
            font-size: 7px;
            letter-spacing: 0.3px;
        }
        .area-summary-table th.left, .area-summary-table td.left {
            text-align: left;
            padding-left: 6px;
        }
        .area-summary-table td {
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        /* --- DETAILED DATA TABLE --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7.5px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th, table.data-table td {
            padding: 3.5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            text-align: center;
        }
        table.data-table th.left, table.data-table td.left {
            text-align: left;
            padding-left: 5px;
        }
        table.data-table thead th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.3px;
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
            border-top: 1.5px solid #059669;
            border-bottom: 1.5px solid #059669;
        }

        /* --- NOTES & OBSERVATIONS --- */
        .notes-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 5px 7px;
            margin-top: 5px;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }
        .notes-box .lbl {
            font-size: 7.5px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }
        .notes-line {
            border-bottom: 1px dotted #94a3b8;
            height: 10px;
            margin-bottom: 2px;
        }

        /* --- SIGNATURES --- */
        .signatures {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            table-layout: fixed;
            margin-top: 6px;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            padding: 0;
            vertical-align: top;
        }
        .sig-container {
            position: relative;
        }
        .sig-role {
            font-size: 7px;
            color: #064e3b;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            margin-bottom: 18px;
            letter-spacing: 0.3px;
        }
        .sig-line {
            border-top: 1px solid #334155;
            padding-top: 2.5px;
        }
        .sig-name {
            font-size: 7px;
            color: #475569;
            text-transform: uppercase;
        }

        /* --- UTILS --- */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    {{-- ========================================================================= --}}
    {{-- OFFICIAL FIXED BOTTOM FOOTER (PERSISTENT ACROSS ALL PAGES)                --}}
    {{-- ========================================================================= --}}
    <footer>
        <table class="footer-table">
            <tr>
                <td class="left">MENRO Tagoloan &bull; Official Monitoring Report</td>
                <td class="right"></td>
            </tr>
        </table>
    </footer>

    {{-- Dynamic Page Numbering in Dompdf --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 7.5;
            $color = array(0.39, 0.45, 0.55);
            $pdf->page_text($pdf->get_width() - 85, $pdf->get_height() - 20, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);
        }
    </script>

@if(!empty($isSinglePage) && $isSinglePage)
    {{-- ========================================================================= --}}
    {{-- 1-PAGE LAYOUT: ALL INFO ON 1 PAGE (FOR 1 TO 5 RECORDS)                    --}}
    {{-- ========================================================================= --}}
    <div>
        {{-- 1. Official Header --}}
        <div class="header-container">
            @if(!empty($headerDataUri))
                <div style="text-align: center; margin: 0; padding: 0;">
                    <img src="{{ $headerDataUri }}" alt="MENRO Tagoloan Letterhead" class="header-img">
                </div>
            @else
                <table class="header-table">
                    <tr>
                        <td class="seal-td">
                            @if(!empty($menroSealDataUri))
                                <img src="{{ $menroSealDataUri }}" alt="MENRO Seal" class="seal-img">
                            @endif
                        </td>
                        <td class="agency-td">
                            <div class="agency-office-top">Municipal Environment and Natural Resources Office</div>
                            <div class="agency-republic">Republic of the Philippines</div>
                            <div class="agency-province">PROVINCE OF MISAMIS ORIENTAL</div>
                            <div class="agency-municipality">MUNICIPALITY OF TAGOLOAN</div>
                        </td>
                        <td class="seal-td">
                            @if(!empty($provinceSealDataUri))
                                <img src="{{ $provinceSealDataUri }}" alt="Province Seal" class="seal-img">
                            @endif
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        {{-- 2. Report Title & Meta --}}
        <div class="report-title-section">
            <h1 class="report-title">PLANTING &amp; MONITORING REPORT</h1>
            <div class="report-subtitle">
                @if($recordCount === 1)
                    Individual Monitoring Record &bull; Official MENRO Document
                @else
                    Environmental Monitoring Summary ({{ $recordCount }} Records) &bull; Official MENRO Document
                @endif
            </div>
            <div class="report-meta">Generated {{ ($generatedAt ?? now())->format('M d, Y g:i A') }}</div>
        </div>

        {{-- 3. Key Performance Indicators --}}
        <div class="section-title">KEY PERFORMANCE INDICATORS</div>
        <table class="kpi-grid">
            <tr>
                <td>
                    <span class="kpi-val">{{ number_format($summary['record_count']) }}</span>
                    <span class="kpi-lbl">MONITORING RECORDS</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['seedlings_planted']) }}</span>
                    <span class="kpi-lbl">SEEDLINGS PLANTED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['survived_count']) }}</span>
                    <span class="kpi-lbl">SURVIVED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['died_count']) }}</span>
                    <span class="kpi-lbl">DIED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['replanted_count']) }}</span>
                    <span class="kpi-lbl">RE-PLANTED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['survival_rate'], 2) }}%</span>
                    <span class="kpi-lbl">SURVIVAL RATE</span>
                </td>
            </tr>
        </table>

        {{-- 4. Survival Rate & Performance Panel --}}
        <div class="section-title">SURVIVAL RATE &amp; MONITORING PERFORMANCE</div>
        <table class="performance-panel">
            <tr>
                <td class="perf-left">
                    <div class="perf-overall-lbl">OVERALL SURVIVAL</div>
                    <div class="perf-overall-val">{{ number_format($totals['survival_rate'], 2) }}%</div>
                    <span class="survival-pill {{ $summary['survival_band'] }}">{{ $summary['survival_band_label'] }}</span>
                    <div class="perf-overall-hint">Excellent: &gt;= 85% | Good: &gt;= 70% | Needs Attention: &lt; 70%</div>
                </td>
                <td class="perf-mid">
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Monitoring events</span>
                        <span class="perf-stat-val">{{ number_format($summary['record_count']) }}</span>
                    </div>
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Distinct sites / areas</span>
                        <span class="perf-stat-val">{{ number_format($summary['site_count']) }}</span>
                    </div>
                </td>
                <td class="perf-right">
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Agencies / requesters</span>
                        <span class="perf-stat-val">{{ number_format($summary['agency_count']) }}</span>
                    </div>
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Coverage period</span>
                        <span class="perf-stat-val">
                            @if($summary['date_from'] && $summary['date_to'])
                                {{ $summary['date_from']->format('M d, Y') }} - {{ $summary['date_to']->format('M d, Y') }}
                            @elseif($summary['date_from'])
                                From {{ $summary['date_from']->format('M d, Y') }}
                            @elseif($summary['date_to'])
                                Until {{ $summary['date_to']->format('M d, Y') }}
                            @else
                                Recorded Entries
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- 5. Monitored Records Table (1 to 5 records) --}}
        <div class="section-title">MONITORED PLANTING RECORD DETAILS</div>
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
                    @php
                        $req = $record->request;
                        $partnerName = $req?->agency?->name ?? $req?->user?->name ?? $req?->requester_name ?? '—';
                        $locationName = !empty($req?->barangay_code)
                            ? \App\Support\TagoloanLocation::barangayName($req->barangay_code)
                            : ($req?->location ?? '—');
                        $rate = $record->seedlings_planted > 0 ? ($record->survived_count / $record->seedlings_planted * 100) : 0;
                    @endphp
                    <tr>
                        <td class="left">{{ optional($req?->request_date)->format('m/d/Y') ?? $req?->created_at?->format('m/d/Y') ?? '—' }}</td>
                        <td class="left">{{ $partnerName }}</td>
                        <td class="left">{{ $locationName }}</td>
                        <td class="left">{{ $record->seedling_type ?: '—' }}</td>
                        <td>{{ optional($record->date_monitoring)->format('m/d/Y') ?? '—' }}</td>
                        <td>{{ number_format($record->seedlings_planted) }}</td>
                        <td>{{ number_format($record->replanted_count) }}</td>
                        <td>{{ number_format($record->survived_count) }}</td>
                        <td>{{ number_format($record->died_count) }}</td>
                        <td>
                            <span style="color: {{ $rate >= 70 ? '#059669' : '#dc2626' }}; font-weight: bold;">
                                {{ number_format($rate, 2) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">No monitoring records match the selected filters.</td></tr>
                @endforelse

                @if($recordCount > 1)
                    <tr class="total-row">
                        <td colspan="5" class="left">OVERALL TOTALS ({{ $recordCount }} RECORDS)</td>
                        <td>{{ number_format($totals['seedlings_planted']) }}</td>
                        <td>{{ number_format($totals['replanted_count']) }}</td>
                        <td>{{ number_format($totals['survived_count']) }}</td>
                        <td>{{ number_format($totals['died_count']) }}</td>
                        <td>{{ number_format($totals['survival_rate'], 2) }}%</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- 6. Field Inspector Notes --}}
        <div class="notes-box">
            <div class="lbl">Additional Notes &amp; Observations</div>
            <div class="notes-line"></div>
            <div class="notes-line"></div>
        </div>

        {{-- 7. Signatures --}}
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
    </div>

@else
    {{-- ========================================================================= --}}
    {{-- 2-PAGE LAYOUT: BULK DOWNLOAD (6+ RECORDS)                                 --}}
    {{-- ========================================================================= --}}

    {{-- ========== PAGE 1: EXECUTIVE ENVIRONMENTAL IMPACT SUMMARY ========== --}}
    <div class="page-break">

        {{-- 1. Official Header --}}
        <div class="header-container">
            @if(!empty($headerDataUri))
                <div style="text-align: center; margin: 0; padding: 0;">
                    <img src="{{ $headerDataUri }}" alt="MENRO Tagoloan Letterhead" class="header-img">
                </div>
            @else
                <table class="header-table">
                    <tr>
                        <td class="seal-td">
                            @if(!empty($menroSealDataUri))
                                <img src="{{ $menroSealDataUri }}" alt="MENRO Seal" class="seal-img">
                            @endif
                        </td>
                        <td class="agency-td">
                            <div class="agency-office-top">Municipal Environment and Natural Resources Office</div>
                            <div class="agency-republic">Republic of the Philippines</div>
                            <div class="agency-province">PROVINCE OF MISAMIS ORIENTAL</div>
                            <div class="agency-municipality">MUNICIPALITY OF TAGOLOAN</div>
                        </td>
                        <td class="seal-td">
                            @if(!empty($provinceSealDataUri))
                                <img src="{{ $provinceSealDataUri }}" alt="Province Seal" class="seal-img">
                            @endif
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        {{-- 2. Report Title & Meta --}}
        <div class="report-title-section">
            <h1 class="report-title">PLANTING &amp; MONITORING REPORT</h1>
            <div class="report-subtitle">Environmental Impact Summary | Official MENRO Document</div>
            <div class="report-meta">Generated {{ ($generatedAt ?? now())->format('M d, Y g:i A') }}</div>
        </div>

        {{-- 3. Key Performance Indicators --}}
        <div class="section-title">KEY PERFORMANCE INDICATORS</div>
        <table class="kpi-grid">
            <tr>
                <td>
                    <span class="kpi-val">{{ number_format($summary['record_count']) }}</span>
                    <span class="kpi-lbl">MONITORING RECORDS</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['seedlings_planted']) }}</span>
                    <span class="kpi-lbl">SEEDLINGS PLANTED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['survived_count']) }}</span>
                    <span class="kpi-lbl">SURVIVED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['died_count']) }}</span>
                    <span class="kpi-lbl">DIED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['replanted_count']) }}</span>
                    <span class="kpi-lbl">RE-PLANTED</span>
                </td>
                <td>
                    <span class="kpi-val">{{ number_format($totals['survival_rate'], 2) }}%</span>
                    <span class="kpi-lbl">SURVIVAL RATE</span>
                </td>
            </tr>
        </table>

        {{-- 4. Survival Rate & Monitoring Performance --}}
        <div class="section-title">SURVIVAL RATE &amp; MONITORING PERFORMANCE</div>
        <table class="performance-panel">
            <tr>
                <td class="perf-left">
                    <div class="perf-overall-lbl">OVERALL SURVIVAL</div>
                    <div class="perf-overall-val">{{ number_format($totals['survival_rate'], 2) }}%</div>
                    <span class="survival-pill {{ $summary['survival_band'] }}">{{ $summary['survival_band_label'] }}</span>
                    <div class="perf-overall-hint">Excellent: &gt;= 85% | Good: &gt;= 70% | Needs Attention: &lt; 70%</div>
                </td>
                <td class="perf-mid">
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Monitoring events</span>
                        <span class="perf-stat-val">{{ number_format($summary['record_count']) }}</span>
                    </div>
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Distinct sites / areas</span>
                        <span class="perf-stat-val">{{ number_format($summary['site_count']) }}</span>
                    </div>
                </td>
                <td class="perf-right">
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Agencies / requesters</span>
                        <span class="perf-stat-val">{{ number_format($summary['agency_count']) }}</span>
                    </div>
                    <div class="perf-stat-group">
                        <span class="perf-stat-lbl">Coverage period</span>
                        <span class="perf-stat-val">
                            @if($summary['date_from'] && $summary['date_to'])
                                {{ $summary['date_from']->format('M d, Y') }} - {{ $summary['date_to']->format('M d, Y') }}
                            @elseif($summary['date_from'])
                                From {{ $summary['date_from']->format('M d, Y') }}
                            @elseif($summary['date_to'])
                                Until {{ $summary['date_to']->format('M d, Y') }}
                            @else
                                Overall Summary
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- 5. Key Findings --}}
        <div class="section-title">KEY FINDINGS</div>
        <ul class="findings-list">
            @forelse($summary['findings'] as $finding)
                <li>{{ $finding }}</li>
            @empty
                <li>No significant findings identified for the current filters.</li>
            @endforelse
        </ul>

        {{-- 6. Geographic / Area Summary --}}
        <div class="section-title">GEOGRAPHIC / AREA SUMMARY</div>
        <table class="area-summary-table">
            <thead>
                <tr>
                    <th class="left" style="width: 36%;">Area / Barangay</th>
                    <th style="width: 12%;">Records</th>
                    <th style="width: 15%;">Planted</th>
                    <th style="width: 15%;">Survived</th>
                    <th style="width: 10%;">Died</th>
                    <th style="width: 12%;">Survival %</th>
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

    </div>

    {{-- ========== PAGE 2: DETAILED RECORDS, OBSERVATIONS & SIGNATURES ========== --}}
    <div>
        <div style="width: 100%; border-bottom: 2px solid #059669; padding-bottom: 4px; margin-bottom: 6px;">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <td style="text-align: left; vertical-align: bottom; padding: 0;">
                        <div style="font-size: 11px; font-weight: bold; color: #064e3b; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">MENRO TAGOLOAN</div>
                        <div style="font-size: 8px; color: #475569; letter-spacing: 0.3px; line-height: 1.2; margin-top: 1px;">Planting &amp; Monitoring Detailed Records</div>
                    </td>
                    <td style="text-align: right; vertical-align: bottom; padding: 0;">
                        <div style="font-size: 7.5px; color: #64748b;">Generated: {{ ($generatedAt ?? now())->format('M d, Y g:i A') }}</div>
                    </td>
                </tr>
            </table>
        </div>

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
                    @php
                        $req = $record->request;
                        $partnerName = $req?->agency?->name ?? $req?->user?->name ?? $req?->requester_name ?? '—';
                        $locationName = !empty($req?->barangay_code)
                            ? \App\Support\TagoloanLocation::barangayName($req->barangay_code)
                            : ($req?->location ?? '—');
                        $rate = $record->seedlings_planted > 0 ? ($record->survived_count / $record->seedlings_planted * 100) : 0;
                    @endphp
                    <tr>
                        <td class="left">{{ optional($req?->request_date)->format('m/d/Y') ?? $req?->created_at?->format('m/d/Y') ?? '—' }}</td>
                        <td class="left">{{ $partnerName }}</td>
                        <td class="left">{{ $locationName }}</td>
                        <td class="left">{{ $record->seedling_type ?: '—' }}</td>
                        <td>{{ optional($record->date_monitoring)->format('m/d/Y') ?? '—' }}</td>
                        <td>{{ number_format($record->seedlings_planted) }}</td>
                        <td>{{ number_format($record->replanted_count) }}</td>
                        <td>{{ number_format($record->survived_count) }}</td>
                        <td>{{ number_format($record->died_count) }}</td>
                        <td>
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
    </div>
@endif

</body>
</html>
