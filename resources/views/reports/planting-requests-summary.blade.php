<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting Requests Summary Report</title>
    <style>
        @page {
            margin: 24px 30px 26px 30px;
            size: portrait;
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

        /* --- HEADER --- */
        .header-container {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2.5px solid #059669;
            padding-bottom: 8px;
        }
        .header-img {
            width: 100%;
            height: auto;
            max-height: 75px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .seal-td {
            width: 55px;
            text-align: center;
        }
        .seal-img {
            width: 48px;
            height: 48px;
        }
        .agency-td {
            text-align: center;
            padding: 0 8px;
        }
        .agency-republic {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .agency-name {
            font-size: 9.5px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.3px;
        }
        .agency-office {
            font-size: 12px;
            font-weight: bold;
            color: #064e3b;
            margin-top: 1px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .agency-sub {
            font-size: 7.5px;
            color: #059669;
            font-style: italic;
        }

        /* --- TITLE --- */
        .doc-title-bar {
            background-color: #064e3b;
            color: #ffffff;
            text-align: center;
            padding: 5px 10px;
            border-radius: 4px;
            margin-bottom: 7px;
        }
        .doc-title {
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 7.5px;
            color: #a7f3d0;
            margin-top: 2px;
        }

        /* --- META BAR --- */
        .meta-bar {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #059669;
            padding: 4px 8px;
            margin-bottom: 10px;
            font-size: 7.5px;
            color: #475569;
            border-radius: 3px;
        }
        .meta-bar strong { color: #0f172a; font-weight: bold; }

        /* --- KPI WIDGETS --- */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin: 0 -5px 10px -5px;
            table-layout: fixed;
        }
        .kpi-table td {
            width: 20%;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #059669;
            border-radius: 4px;
            padding: 6px 3px;
            text-align: center;
        }
        .kpi-val { display: block; font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 1px; }
        .kpi-lbl { display: block; font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; font-weight: 600; }
        .kpi-sub { display: block; font-size: 6.5px; color: #94a3b8; margin-top: 1px; }

        /* --- DATA TABLE --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #064e3b;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table td.left { text-align: left; }
        .data-table tbody tr.parent-row { background-color: #ffffff; }
        .data-table tbody tr.sub-row { background-color: #f8fafc; }
        .data-table tbody tr.total-row {
            background-color: #ecfdf5;
            font-weight: bold;
            color: #064e3b;
            border-top: 2px solid #059669;
        }

        /* --- SUB REQUEST FORMATTING --- */
        .sub-arrow {
            color: #059669;
            font-weight: bold;
            font-size: 9px;
            display: inline-block;
            margin-right: 2px;
        }
        .sub-no {
            font-family: "Courier New", monospace;
            font-weight: bold;
            color: #334155;
        }
        .req-no {
            font-family: "Courier New", monospace;
            font-weight: bold;
            color: #0f172a;
        }

        /* --- BADGES --- */
        .badge-sub {
            display: inline-block;
            font-size: 6px;
            font-weight: bold;
            color: #475569;
            background-color: #e2e8f0;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 1px 3px;
            margin-left: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-parent {
            display: inline-block;
            font-size: 6px;
            font-weight: bold;
            color: #065f46;
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 3px;
            padding: 1px 3px;
            margin-left: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* --- STATUS PILL --- */
        .status-pill {
            display: inline-block;
            padding: 1.5px 5px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 6px;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        .status-approved { background-color: #059669; }
        .status-pending { background-color: #d97706; }
        .status-in_progress { background-color: #0284c7; }
        .status-completed { background-color: #15803d; }
        .status-rejected { background-color: #dc2626; }

        /* --- SIGNATURES --- */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .sig-table td {
            width: 33.33%;
            padding: 0 10px;
            vertical-align: top;
            text-align: center;
        }
        .sig-role {
            font-size: 7.5px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            margin-bottom: 24px;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .sig-line {
            border-top: 1px solid #1e293b;
            padding-top: 3px;
        }
        .sig-name {
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7px;
            color: #94a3b8;
        }
        .footer-table td.right { text-align: right; }
    </style>
</head>
<body>

    {{-- ========== OFFICIAL MENRO HEADER ========== --}}
    <div class="header-container">
        @if(!empty($headerDataUri))
            <div style="text-align: center;">
                <img src="{{ $headerDataUri }}" alt="MENRO Tagoloan Letterhead" class="header-img">
            </div>
        @else
            <table class="header-table">
                <tr>
                    <td class="seal-td">
                        @if(!empty($provinceSealDataUri))
                            <img src="{{ $provinceSealDataUri }}" alt="Province Seal" class="seal-img">
                        @endif
                    </td>
                    <td class="agency-td">
                        <div class="agency-republic">Republic of the Philippines</div>
                        <div class="agency-name">PROVINCE OF MISAMIS ORIENTAL</div>
                        <div class="agency-name">MUNICIPALITY OF TAGOLOAN</div>
                        <div class="agency-office">Municipal Environment and Natural Resources Office</div>
                        <div class="agency-sub">Planting Requests &amp; Project Summary Monitoring</div>
                    </td>
                    <td class="seal-td">
                        @if(!empty($menroSealDataUri))
                            <img src="{{ $menroSealDataUri }}" alt="MENRO Seal" class="seal-img">
                        @endif
                    </td>
                </tr>
            </table>
        @endif
    </div>

    {{-- ========== TITLE ========== --}}
    <div class="doc-title-bar">
        <div class="doc-title">Planting Requests Summary Report</div>
        <div class="doc-subtitle">Consolidated Requests &amp; Seedling Target Allocations &bull; Tagoloan, Misamis Oriental</div>
    </div>

    <div class="meta-bar">
        Generated: <strong>{{ $generatedAt->format('M d, Y g:i A') }}</strong>
        &nbsp;|&nbsp; Reports: <strong>{{ $totals['total_requests'] }} Total</strong> ({{ $totals['parent_requests_count'] }} Main, {{ $totals['sub_requests_count'] }} Sub-request{{ $totals['sub_requests_count'] != 1 ? 's' : '' }})
        @if(!empty($filterNote))
            &nbsp;|&nbsp; Filter: <strong>{{ $filterNote }}</strong>
        @endif
    </div>

    {{-- ========== KPI METRIC CARDS ========== --}}
    <table class="kpi-table">
        <tr>
            <td>
                <span class="kpi-val">{{ number_format($totals['total_requests']) }}</span>
                <span class="kpi-lbl">Total Requests</span>
                <span class="kpi-sub">{{ $totals['parent_requests_count'] }} Main &bull; {{ $totals['sub_requests_count'] }} Sub</span>
            </td>
            <td>
                <span class="kpi-val">{{ number_format($totals['target_trees']) }}</span>
                <span class="kpi-lbl">Target Seedlings</span>
                <span class="kpi-sub">Total Trees</span>
            </td>
            <td>
                <span class="kpi-val">{{ number_format($totals['approved_requests']) }}</span>
                <span class="kpi-lbl">Approved</span>
                <span class="kpi-sub">Ready to Plant</span>
            </td>
            <td>
                <span class="kpi-val">{{ number_format($totals['in_progress_requests']) }}</span>
                <span class="kpi-lbl">In Progress</span>
                <span class="kpi-sub">Active Operations</span>
            </td>
            <td>
                <span class="kpi-val">{{ number_format($totals['pending_requests']) }}</span>
                <span class="kpi-lbl">Pending Review</span>
                <span class="kpi-sub">Awaiting Action</span>
            </td>
        </tr>
    </table>

    {{-- ========== DETAILED REQUESTS TABLE ========== --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 9%;">Req No.</th>
                <th class="left" style="width: 25%;">Project Name &amp; Structure</th>
                <th class="left" style="width: 16%;">Partner / Requester</th>
                <th class="left" style="width: 14%;">Barangay / Site</th>
                <th style="width: 8%;">Habitat</th>
                <th style="width: 9%;">Target Trees</th>
                <th style="width: 9%;">Date Filed</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $req = $row->request;
                    $isSub = $row->is_sub_request;
                    $sRaw = (string)($req->status ?? 'pending');
                    $sNormalized = str_replace(' ', '_', strtolower($sRaw));
                    $sLabels = [
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ];
                @endphp
                <tr class="{{ $isSub ? 'sub-row' : 'parent-row' }}">
                    <td>
                        @if($isSub)
                            <span class="sub-arrow">&#8627;</span><span class="sub-no">{{ $req->request_no ?? 'REQ-' . $req->id }}</span>
                        @else
                            <span class="req-no">{{ $req->request_no ?? 'REQ-' . $req->id }}</span>
                        @endif
                    </td>
                    <td class="left">
                        <div style="{{ $isSub ? 'padding-left: 6px;' : '' }}">
                            <span style="font-weight: bold; color: {{ $isSub ? '#334155' : '#0f172a' }};">
                                {{ $req->project_name }}
                            </span>
                            @if($isSub)
                                <span class="badge-sub">Sub-request</span>
                            @elseif($row->children_count > 0)
                                <span class="badge-parent">{{ $row->children_count }} {{ $row->children_count === 1 ? 'sub-request' : 'sub-requests' }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="left">{{ $req->agency?->name ?? $req->requester_name ?? '-' }}</td>
                    <td class="left">{{ $req->barangay_code ? \App\Support\TagoloanLocation::barangayName($req->barangay_code) : ($req->location ?? '-') }}</td>
                    <td>{{ ucfirst($req->habitat?->value ?? 'Terrestrial') }}</td>
                    <td><strong>{{ number_format((int)$req->target_trees) }}</strong></td>
                    <td>{{ optional($req->request_date)->format('m/d/Y') ?? $req->created_at->format('m/d/Y') }}</td>
                    <td>
                        <span class="status-pill status-{{ $sNormalized }}">{{ $sLabels[$sNormalized] ?? ucfirst($sRaw) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No planting requests found matching the specified filters.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5" class="left">
                    TOTAL CONSOLIDATED REPORT ({{ $totals['total_requests'] }} {{ $totals['total_requests'] === 1 ? 'Report' : 'Reports' }} in Total: {{ $totals['parent_requests_count'] }} Main, {{ $totals['sub_requests_count'] }} Sub-request{{ $totals['sub_requests_count'] != 1 ? 's' : '' }})
                </td>
                <td>{{ number_format($totals['target_trees']) }}</td>
                <td colspan="2">{{ number_format($totals['total_requests']) }} Records</td>
            </tr>
        </tbody>
    </table>

    {{-- ========== SIGNATURES ========== --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-role">Prepared By</div>
                <div class="sig-line">
                    <div class="sig-name">MENRO Records Officer</div>
                </div>
            </td>
            <td>
                <div class="sig-role">Reviewed By</div>
                <div class="sig-line">
                    <div class="sig-name">Forestry Section Head</div>
                </div>
            </td>
            <td>
                <div class="sig-role">Approved By</div>
                <div class="sig-line">
                    <div class="sig-name">MENRO Officer</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td>MENRO Tagoloan &bull; Official Summary Monitoring Report</td>
            <td class="right">Generated on {{ now()->format('M d, Y') }}</td>
        </tr>
    </table>

</body>
</html>

