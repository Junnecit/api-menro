<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting &amp; Monitoring Report</title>
    <style>
        @page { margin: 14px 14px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #0f172a;
            line-height: 1.35;
        }

        .letterhead { text-align: center; margin: 0 0 8px 0; }
        .letterhead img.header-banner {
            width: 100%;
            height: auto;
        }
        .letterhead-fallback {
            border-collapse: collapse;
            margin: 0 auto 4px auto;
        }
        .letterhead-fallback td { vertical-align: middle; padding: 0; }
        .seal { width: 58px; text-align: center; }
        .seal img { width: 52px; height: 52px; border-radius: 50%; }
        .agency-block { text-align: left; padding-left: 10px; }
        .agency-block .office-title { font-size: 11px; font-weight: bold; color: #047857; }
        .agency-block .republic { font-size: 9px; }
        .agency-block .province,
        .agency-block .municipality { font-size: 9px; font-weight: bold; }

        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 4px 0 0 0;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 9px;
            color: #475569;
            margin: 1px 0 4px 0;
        }
        .meta-line {
            text-align: center;
            font-size: 8px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1.5px solid #047857;
            padding-bottom: 2px;
            margin: 10px 0 6px 0;
        }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin: 0 0 8px 0; }
        .kpi-table td {
            width: 16.66%;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 3px;
            text-align: center;
            padding: 7px 4px;
            vertical-align: middle;
        }
        .kpi-table .kpi-value {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #065f46;
        }
        .kpi-table .kpi-label {
            display: block;
            font-size: 7px;
            color: #475569;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .highlight-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .highlight-table td {
            vertical-align: top;
            padding: 8px 10px;
            border: 1px solid #a7f3d0;
            background: #f0fdf4;
        }
        .highlight-table .survival-cell { width: 32%; text-align: center; background: #ecfdf5; }
        .survival-rate {
            font-size: 22px;
            font-weight: bold;
            color: #065f46;
            line-height: 1.1;
        }
        .survival-band {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 8px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 10px;
            color: #fff;
            background: #047857;
        }
        .survival-band.good { background: #0f766e; }
        .survival-band.attention { background: #b45309; }
        .perf-grid { width: 100%; border-collapse: collapse; }
        .perf-grid td {
            width: 50%;
            font-size: 9px;
            padding: 2px 0;
            border: none;
            background: transparent;
        }
        .perf-grid .label { color: #64748b; }
        .perf-grid .value { font-weight: bold; color: #0f172a; }

        .findings { margin: 0 0 4px 14px; padding: 0; }
        .findings li { margin-bottom: 3px; font-size: 9px; }

        table.geo, table.report {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.geo th, table.geo td,
        table.report th, table.report td {
            border: 1px solid #94a3b8;
            padding: 4px 5px;
            font-size: 8px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: middle;
        }
        table.geo thead th, table.report thead th {
            background: #ecfdf5;
            color: #065f46;
            text-align: center;
            font-weight: bold;
            font-size: 7.5px;
            line-height: 1.25;
        }
        table.geo td, table.report td { text-align: center; }
        table.report td.left,
        table.geo td.left {
            text-align: left;
            padding-left: 6px;
        }
        table.report td.num {
            text-align: center;
            white-space: nowrap;
        }
        tr.total-row td {
            font-weight: bold;
            background: #f0fdf4;
            border-top: 1.5px solid #047857;
        }

        .page-break { page-break-after: always; }

        .compact-header {
            text-align: center;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #a7f3d0;
        }
        .compact-header img {
            width: 100%;
            height: auto;
        }
        .compact-title {
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .notes-box {
            margin-top: 10px;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            min-height: 48px;
        }
        .notes-box .label {
            font-size: 9px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .notes-box .hint {
            font-size: 8px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .notes-line {
            border-bottom: 1px dotted #94a3b8;
            height: 14px;
            margin-bottom: 4px;
        }

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .signatures td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-role {
            font-size: 8px;
            font-weight: bold;
            color: #047857;
            text-transform: uppercase;
            margin-bottom: 28px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            margin: 0 8px;
            padding-top: 4px;
            font-size: 8px;
            color: #475569;
        }
        .sig-date {
            margin-top: 14px;
            font-size: 8px;
            color: #64748b;
        }
        .sig-date-line {
            display: inline-block;
            border-bottom: 1px solid #94a3b8;
            min-width: 90px;
            height: 12px;
            vertical-align: bottom;
        }

        .footer-meta {
            margin-top: 10px;
            font-size: 7px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>
    {{-- ========== PAGE 1: Executive Summary ========== --}}
    <div class="page-break">
        <div class="letterhead">
            @if(!empty($headerDataUri))
                <img class="header-banner" src="{{ $headerDataUri }}" alt="MENRO Letterhead">
            @else
                <table class="letterhead-fallback">
                    <tr>
                        <td class="seal">
                            @if(!empty($menroSealDataUri))
                                <img src="{{ $menroSealDataUri }}" alt="MENRO Seal">
                            @elseif(!empty($provinceSealDataUri))
                                <img src="{{ $provinceSealDataUri }}" alt="Province Seal">
                            @endif
                        </td>
                        <td class="agency-block">
                            <div class="office-title">Municipal Environment and Natural Resources Office</div>
                            <div class="republic">Republic of the Philippines</div>
                            <div class="province">PROVINCE OF MISAMIS ORIENTAL</div>
                            <div class="municipality">MUNICIPALITY OF TAGOLOAN</div>
                        </td>
                    </tr>
                </table>
            @endif
        </div>

        <div class="doc-title">Planting &amp; Monitoring Report</div>
        <div class="doc-subtitle">Environmental Impact Summary | Official MENRO Document</div>
        <div class="meta-line">
            Generated {{ $generatedAt->format('M d, Y g:i A') }}
            @if(!empty($filterNote))
                | {{ $filterNote }}
            @endif
        </div>

        <div class="section-title">Key Performance Indicators</div>
        <table class="kpi-table">
            <tr>
                <td>
                    <span class="kpi-value">{{ number_format($summary['record_count']) }}</span>
                    <span class="kpi-label">Monitoring Records</span>
                </td>
                <td>
                    <span class="kpi-value">{{ number_format($totals['seedlings_planted']) }}</span>
                    <span class="kpi-label">Seedlings Planted</span>
                </td>
                <td>
                    <span class="kpi-value">{{ number_format($totals['survived_count']) }}</span>
                    <span class="kpi-label">Survived</span>
                </td>
                <td>
                    <span class="kpi-value">{{ number_format($totals['died_count']) }}</span>
                    <span class="kpi-label">Died</span>
                </td>
                <td>
                    <span class="kpi-value">{{ number_format($totals['replanted_count']) }}</span>
                    <span class="kpi-label">Re-planted</span>
                </td>
                <td>
                    <span class="kpi-value">{{ number_format($totals['survival_rate'], 2) }}%</span>
                    <span class="kpi-label">Survival Rate</span>
                </td>
            </tr>
        </table>

        <div class="section-title">Survival Rate &amp; Monitoring Performance</div>
        <table class="highlight-table">
            <tr>
                <td class="survival-cell">
                    <div style="font-size:8px;color:#64748b;text-transform:uppercase;">Overall Survival</div>
                    <div class="survival-rate">{{ number_format($totals['survival_rate'], 2) }}%</div>
                    <span class="survival-band {{ $summary['survival_band'] }}">{{ $summary['survival_band_label'] }}</span>
                    <div style="font-size:7px;color:#64748b;margin-top:6px;">
                        Excellent &gt;= 85% | Good &gt;= 70% | Needs Attention &lt; 70%
                    </div>
                </td>
                <td>
                    <table class="perf-grid">
                        <tr>
                            <td>
                                <span class="label">Monitoring events</span><br>
                                <span class="value">{{ number_format($summary['record_count']) }}</span>
                            </td>
                            <td>
                                <span class="label">Agencies / requesters</span><br>
                                <span class="value">{{ number_format($summary['agency_count']) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="label">Distinct sites / areas</span><br>
                                <span class="value">{{ number_format($summary['site_count']) }}</span>
                            </td>
                            <td>
                                <span class="label">Coverage period</span><br>
                                <span class="value">
                                    @if($summary['date_from'] && $summary['date_to'])
                                        {{ $summary['date_from']->format('M d, Y') }}
                                        -
                                        {{ $summary['date_to']->format('M d, Y') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="section-title">Key Findings</div>
        <ul class="findings">
            @foreach($summary['findings'] as $finding)
                <li>{{ $finding }}</li>
            @endforeach
        </ul>

        <div class="section-title">Geographic / Area Summary</div>
        <table class="geo">
            <thead>
                <tr>
                    <th style="width:36%">Area / Barangay</th>
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
                        <td style="text-align:left">{{ $area['area'] }}</td>
                        <td>{{ $area['records'] }}</td>
                        <td>{{ number_format($area['seedlings_planted']) }}</td>
                        <td>{{ number_format($area['survived_count']) }}</td>
                        <td>{{ number_format($area['died_count']) }}</td>
                        <td>{{ number_format($area['survival_rate'], 2) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No geographic data available for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ========== PAGE 2: Detailed Records ========== --}}
    <div>
        <div class="compact-header">
            @if(!empty($headerDataUri))
                <img src="{{ $headerDataUri }}" alt="MENRO Letterhead">
            @endif
            <div class="compact-title">Detailed Planting &amp; Monitoring Records</div>
        </div>

        <table class="report">
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
                    <th rowspan="2">Date Planted</th>
                    <th rowspan="2">Agency / Requester</th>
                    <th rowspan="2">Area Planted</th>
                    <th rowspan="2">Seedling Type</th>
                    <th colspan="6">Monitoring</th>
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
                        <td class="num">{{ optional($record->request?->request_date)->format('m/d/Y') }}</td>
                        <td class="left">{{ $record->request?->agency?->name ?? $record->request?->requester_name ?? '-' }}</td>
                        <td class="left">{{ $record->request?->location }}</td>
                        <td class="left">{{ $record->seedling_type }}</td>
                        <td class="num">{{ optional($record->date_monitoring)->format('m/d/Y') }}</td>
                        <td class="num">{{ $record->seedlings_planted }}</td>
                        <td class="num">{{ $record->replanted_count }}</td>
                        <td class="num">{{ $record->survived_count }}</td>
                        <td class="num">{{ $record->died_count }}</td>
                        <td class="num">
                            {{ $record->seedlings_planted > 0
                                ? number_format($record->survived_count / $record->seedlings_planted * 100, 2)
                                : '0.00' }}%
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">No monitoring records match the selected filters.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="5" class="left">Total</td>
                    <td class="num">{{ $totals['seedlings_planted'] }}</td>
                    <td class="num">{{ $totals['replanted_count'] }}</td>
                    <td class="num">{{ $totals['survived_count'] }}</td>
                    <td class="num">{{ $totals['died_count'] }}</td>
                    <td class="num">{{ number_format($totals['survival_rate'], 2) }}%</td>
                </tr>
            </tbody>
        </table>

        <div class="notes-box">
            <div class="label">Notes</div>
            <div class="hint">
                Figures are based on recorded planting and monitoring data. Additional observations or corrective actions may be noted below.
            </div>
            <div class="notes-line"></div>
            <div class="notes-line"></div>
            <div class="notes-line"></div>
        </div>

        <table class="signatures">
            <tr>
                <td>
                    <div class="sig-role">Prepared by</div>
                    <div class="sig-line">Signature over Printed Name</div>
                    <div class="sig-date">Date: <span class="sig-date-line"></span></div>
                </td>
                <td>
                    <div class="sig-role">Reviewed by</div>
                    <div class="sig-line">Signature over Printed Name</div>
                    <div class="sig-date">Date: <span class="sig-date-line"></span></div>
                </td>
                <td>
                    <div class="sig-role">Approved by</div>
                    <div class="sig-line">Signature over Printed Name</div>
                    <div class="sig-date">Date: <span class="sig-date-line"></span></div>
                </td>
            </tr>
        </table>

        <div class="footer-meta">MENRO Tagoloan | Planting &amp; Monitoring Report | Generated {{ $generatedAt->format('M d, Y g:i A') }}</div>
    </div>
</body>
</html>
