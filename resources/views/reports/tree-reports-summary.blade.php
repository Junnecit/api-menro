<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Tree & Field Incident Reports Summary</title>
    <style>
        @page {
            margin: 20px 28px 32px 28px;
            size: legal portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.35;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* --- FIXED BOTTOM FOOTER --- */
        footer {
            position: fixed;
            bottom: -22px;
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
            max-width: 100%;
            height: auto;
            max-height: 62px;
            display: block;
            margin: 0 auto;
        }
        .header-fallback {
            text-align: center;
            line-height: 1.15;
        }
        .header-fallback .republic {
            font-size: 8px;
            letter-spacing: 0.5px;
            color: #475569;
            text-transform: uppercase;
        }
        .header-fallback .office {
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            margin-top: 1px;
        }
        .header-fallback .location {
            font-size: 8px;
            color: #64748b;
        }

        /* --- DOCUMENT TITLE STRIP --- */
        .title-strip {
            margin-top: 4px;
            margin-bottom: 6px;
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 4px;
            padding: 5px 8px;
        }
        .title-strip table {
            width: 100%;
            border-collapse: collapse;
        }
        .title-strip td {
            padding: 0;
            vertical-align: middle;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            color: #065f46;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 7.5px;
            color: #475569;
            margin-top: 1px;
        }
        .meta-right {
            text-align: right;
            font-size: 7.5px;
            color: #475569;
            line-height: 1.3;
        }
        .meta-right strong {
            color: #0f172a;
        }

        /* --- KPI SUMMARY GRID --- */
        .kpi-container {
            width: 100%;
            margin-bottom: 7px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kpi-table td {
            padding: 0 2px;
            vertical-align: top;
        }
        .kpi-table td:first-child {
            padding-left: 0;
        }
        .kpi-table td:last-child {
            padding-right: 0;
        }
        .kpi-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 4px 6px;
            background-color: #f8fafc;
            text-align: center;
        }
        .kpi-card.accent {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
        }
        .kpi-card.warning {
            background-color: #fef3c7;
            border-color: #fde68a;
        }
        .kpi-card.danger {
            background-color: #fee2e2;
            border-color: #fecaca;
        }
        .kpi-card.resolved {
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }
        .kpi-num {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.1;
        }
        .kpi-card.accent .kpi-num {
            color: #047857;
        }
        .kpi-card.warning .kpi-num {
            color: #b45309;
        }
        .kpi-card.danger .kpi-num {
            color: #b91c1c;
        }
        .kpi-card.resolved .kpi-num {
            color: #1d4ed8;
        }
        .kpi-label {
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
            margin-top: 1px;
        }

        /* --- MAIN DATA TABLE --- */
        .table-wrap {
            margin-top: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
        }
        table.data-table th {
            background-color: #065f46;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 4px 5px;
            text-transform: uppercase;
            font-size: 7px;
            border: 0.5px solid #047857;
            white-space: nowrap;
        }
        table.data-table td {
            padding: 4px 5px;
            border: 0.5px solid #cbd5e1;
            vertical-align: top;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-critical { background-color: #fee2e2; color: #991b1b; }
        .badge-high { background-color: #ffedd5; color: #9a3412; }
        .badge-medium { background-color: #fef3c7; color: #92400e; }
        .badge-low { background-color: #ecfdf5; color: #065f46; }

        .badge-submitted { background-color: #fef3c7; color: #92400e; }
        .badge-under_review { background-color: #e0e7ff; color: #3730a3; }
        .badge-resolved { background-color: #d1fae5; color: #065f46; }
        .badge-dismissed { background-color: #f1f5f9; color: #475569; }

        /* --- SIGNATURE BLOCK --- */
        .signatures {
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 10px;
            font-size: 7.5px;
        }
        .sig-box {
            text-align: center;
        }
        .sig-role {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 22px;
        }
        .sig-line {
            border-bottom: 1px solid #1e293b;
            margin-bottom: 2px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 8.5px;
            color: #0f172a;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 7px;
            color: #475569;
        }
    </style>
</head>
<body>
    <footer>
        <table class="footer-table">
            <tr>
                <td class="left">MENRO Tagoloan &bull; Tree & Field Incident Monitoring System</td>
                <td class="right">Generated on {{ $generatedAt->format('F d, Y h:i A') }}</td>
            </tr>
        </table>
    </footer>

    <div class="header-container">
        @if(!empty($headerDataUri))
            <img src="{{ $headerDataUri }}" class="header-img" alt="MENRO Tagoloan Header">
        @else
            <table class="header-table" style="width: 82%; margin: 0 auto 3px auto; border-collapse: collapse;">
                <tr>
                    <td style="width: 50px; text-align: center; vertical-align: middle;">
                        @if(!empty($menroSealDataUri))
                            <img src="{{ $menroSealDataUri }}" style="width: 44px; height: 44px;" alt="MENRO Seal">
                        @endif
                    </td>
                    <td style="text-align: center; padding: 0 10px; vertical-align: middle;">
                        <div class="header-fallback">
                            <div class="republic">Republic of the Philippines &bull; Province of Misamis Oriental</div>
                            <div class="office">Municipal Environment and Natural Resources Office (MENRO)</div>
                            <div class="location">Municipality of Tagoloan &bull; Official Field Monitoring Registry</div>
                        </div>
                    </td>
                    <td style="width: 50px; text-align: center; vertical-align: middle;">
                        @if(!empty($provinceSealDataUri))
                            <img src="{{ $provinceSealDataUri }}" style="width: 44px; height: 44px;" alt="Province Seal">
                        @endif
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <div class="title-strip">
        <table>
            <tr>
                <td>
                    <div class="doc-title">Field Incident & Tree Inspection Report Log</div>
                    <div class="doc-subtitle">Summary of tree health conditions, storm damages, hazards, and corrective actions</div>
                </td>
                <td class="meta-right">
                    <strong>Total Reports:</strong> {{ number_format($totals['total_reports']) }}<br>
                    <strong>Scope:</strong> {{ $filterNote ?: 'All Active Reports' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="kpi-container">
        <table class="kpi-table">
            <tr>
                <td style="width: 20%;">
                    <div class="kpi-card accent">
                        <div class="kpi-num">{{ number_format($totals['total_reports']) }}</div>
                        <div class="kpi-label">Total Reports</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-card danger">
                        <div class="kpi-num">{{ number_format($totals['critical_count']) }}</div>
                        <div class="kpi-label">Critical / High</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-card warning">
                        <div class="kpi-num">{{ number_format($totals['submitted_count']) }}</div>
                        <div class="kpi-label">Pending Review</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-card resolved">
                        <div class="kpi-num">{{ number_format($totals['resolved_count']) }}</div>
                        <div class="kpi-label">Resolved Issues</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="kpi-card">
                        <div class="kpi-num">{{ number_format($totals['need_replacement_count']) }}</div>
                        <div class="kpi-label">Need Replacement</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Report Code</th>
                    <th style="width: 9%;">Date / Time</th>
                    <th style="width: 14%;">Type & Severity</th>
                    <th style="width: 18%;">Title & Tree Tag</th>
                    <th style="width: 16%;">Location / Barangay</th>
                    <th style="width: 12%;">Reported By</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 13%;">Action / Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td style="font-weight: bold; color: #047857;">{{ $report->report_code ?: ('RPT-'.$report->id) }}</td>
                        <td>{{ $report->created_at ? $report->created_at->format('M d, Y') : '—' }}</td>
                        <td>
                            <div><strong>{{ $report->report_type?->label() ?? $report->report_type }}</strong></div>
                            @php
                                $sev = $report->severity?->value ?? (string)$report->severity;
                            @endphp
                            <span class="badge badge-{{ $sev }}">{{ strtoupper($sev) }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $report->title }}</div>
                            @if($report->tree)
                                <div style="color: #64748b; font-size: 6.5px;">
                                    Tree: {{ $report->tree->tree_code }} ({{ $report->tree->species ?: $report->tree->common_name }})
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $report->barangay ?: 'Tagoloan' }}</div>
                            @if($report->landmark)
                                <div style="color: #64748b; font-size: 6.5px;">Ref: {{ $report->landmark }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $report->reportedBy?->name ?? 'Field Officer' }}</div>
                            <div style="color: #64748b; font-size: 6.5px;">{{ $report->agency?->name ?? 'MENRO' }}</div>
                        </td>
                        <td>
                            @php
                                $stat = $report->status?->value ?? (string)$report->status;
                            @endphp
                            <span class="badge badge-{{ $stat }}">{{ strtoupper(str_replace('_', ' ', $stat)) }}</span>
                        </td>
                        <td>
                            <div>{{ \Illuminate\Support\Str::limit($report->action_taken ?: $report->resolution_notes ?: $report->description, 60) ?: '—' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 14px; color: #64748b;">
                            No field incident or tree reports found matching the criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signatures">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-box">
                        <div class="sig-role">Prepared by:</div>
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ auth()->user()?->name ?? 'Field Monitoring Officer' }}</div>
                        <div class="sig-title">Field Inspector / Officer</div>
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <div class="sig-role">Reviewed by:</div>
                        <div class="sig-line"></div>
                        <div class="sig-name">MENRO Inspection Team</div>
                        <div class="sig-title">Monitoring & Evaluation Section</div>
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <div class="sig-role">Approved by:</div>
                        <div class="sig-line"></div>
                        <div class="sig-name">MENRO Officer</div>
                        <div class="sig-title">Municipal Environment & Natural Resources Officer</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
