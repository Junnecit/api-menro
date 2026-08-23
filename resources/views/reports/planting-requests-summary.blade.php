<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting Requests Summary Report</title>
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
            padding-right: 65px; /* Room for dynamic page number */
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
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .seal-td {
            width: 62px;
            text-align: center;
        }
        .seal-img {
            width: 56px;
            height: 56px;
        }
        .agency-td {
            text-align: center;
            padding: 0 10px;
        }
        .agency-republic {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            line-height: 1.2;
        }
        .agency-province {
            font-size: 9.5px;
            color: #334155;
            font-weight: bold;
            letter-spacing: 0.3px;
            line-height: 1.2;
            margin-top: 1px;
        }
        .agency-municipality {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            line-height: 1.2;
            margin-top: 1px;
        }
        .agency-office {
            font-size: 13.5px;
            font-weight: bold;
            color: #064e3b;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .agency-tagline {
            font-size: 8px;
            color: #059669;
            font-style: italic;
            margin-top: 1px;
        }

        /* --- TITLE --- */
        .doc-title-bar {
            background-color: #064e3b;
            color: #ffffff;
            text-align: center;
            padding: 5px 12px;
            border-radius: 3px;
            margin-bottom: 6px;
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
            margin-top: 1.5px;
        }

        /* --- META BAR --- */
        .meta-bar {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #059669;
            border-radius: 3px;
            padding: 4px 8px;
            margin-bottom: 8px;
            font-size: 8px;
            color: #475569;
        }
        .meta-bar strong { color: #0f172a; font-weight: bold; }

        /* --- KPI WIDGETS --- */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            table-layout: fixed;
            margin-bottom: 8px;
        }
        .kpi-table td {
            width: 20%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 2.5px solid #059669;
            border-radius: 3px;
            padding: 6px 3px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-val { display: block; font-size: 12.5px; font-weight: bold; color: #0f172a; line-height: 1.1; }
        .kpi-lbl { display: block; font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; font-weight: bold; margin-top: 2px; }
        .kpi-sub { display: block; font-size: 6.5px; color: #94a3b8; margin-top: 1px; }

        /* --- DATA TABLE --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th, table.data-table td {
            padding: 4.5px 5px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: center;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.4px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1.5px solid #94a3b8;
        }
        table.data-table td.left, table.data-table th.left { text-align: left; }
        table.data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        table.data-table tbody tr.parent-row { background-color: #ffffff; }
        table.data-table tbody tr.sub-row { background-color: #f8fafc; }
        table.data-table tbody tr.total-row {
            background-color: #ecfdf5;
        }
        table.data-table tbody tr.total-row td {
            font-weight: bold;
            color: #064e3b;
            border-top: 1.5px solid #059669;
            border-bottom: 1.5px solid #059669;
        }

        /* --- SUB REQUEST FORMATTING --- */
        .sub-arrow {
            color: #059669;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
            margin-right: 3px;
        }
        .sub-no {
            font-family: "Helvetica", "Arial", sans-serif;
            font-weight: bold;
            color: #475569;
        }
        .req-no {
            font-family: "Helvetica", "Arial", sans-serif;
            font-weight: bold;
            color: #0f172a;
        }

        /* --- BADGES --- */
        .badge-sub {
            display: inline-block;
            font-size: 6.5px;
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
            font-size: 6.5px;
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
            padding: 1.5px 6px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 8px;
            color: #ffffff;
            letter-spacing: 0.3px;
            background-color: #64748b;
        }
        .status-approved { background-color: #059669; }
        .status-pending { background-color: #d97706; }
        .status-in_progress { background-color: #0284c7; }
        .status-completed { background-color: #15803d; }
        .status-rejected { background-color: #dc2626; }

        /* --- SIGNATURES --- */
        .signatures {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            table-layout: fixed;
            margin-top: 14px;
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
            font-size: 7.5px;
            color: #064e3b;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            margin-bottom: 20px;
            letter-spacing: 0.4px;
        }
        .sig-line {
            border-top: 1px solid #334155;
            padding-top: 3px;
        }
        .sig-name {
            font-size: 7.5px;
            color: #475569;
            text-transform: uppercase;
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
                <td class="left">MENRO Tagoloan &bull; Official Planting Requests Summary</td>
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

    {{-- ========== OFFICIAL MENRO HEADER ========== --}}
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
                        <div class="agency-republic">Republic of the Philippines</div>
                        <div class="agency-province">Province of Misamis Oriental</div>
                        <div class="agency-municipality">MUNICIPALITY OF TAGOLOAN</div>
                        <div class="agency-office">Municipal Environment and Natural Resources Office</div>
                        <div class="agency-tagline">MENRO Tagoloan &bull; Protecting &amp; Preserving Our Natural Resources</div>
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

    {{-- ========== TITLE ========== --}}
    <div class="doc-title-bar">
        <h1 class="doc-title">Planting Requests Summary Report</h1>
        <div class="doc-subtitle">Consolidated Requests &amp; Seedling Target Allocations &bull; Tagoloan, Misamis Oriental</div>
    </div>

    <div class="meta-bar">
        Generated: <strong>{{ $generatedAt->format('M d, Y g:i A') }}</strong>
        &nbsp;|&nbsp; Scope: <strong>{{ $totals['total_requests'] }} Total Entries</strong> ({{ $totals['parent_requests_count'] }} Main, {{ $totals['sub_requests_count'] }} Sub-request{{ $totals['sub_requests_count'] != 1 ? 's' : '' }})
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
        <colgroup>
            <col style="width: 10%">
            <col style="width: 24%">
            <col style="width: 16%">
            <col style="width: 15%">
            <col style="width: 9%">
            <col style="width: 8%">
            <col style="width: 9%">
            <col style="width: 9%">
        </colgroup>
        <thead>
            <tr>
                <th class="left">Req No.</th>
                <th class="left">Project Name &amp; Structure</th>
                <th class="left">Partner / Requester</th>
                <th class="left">Barangay / Site</th>
                <th>Habitat</th>
                <th>Target Trees</th>
                <th>Date Filed</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $req = $row->request;
                    $isSub = $row->is_sub_request;
                    $partnerName = $req?->agency?->name ?? $req?->user?->name ?? $req?->requester_name ?? '—';
                    $locationName = !empty($req?->barangay_code)
                        ? \App\Support\TagoloanLocation::barangayName($req->barangay_code)
                        : ($req?->location ?? '—');
                    $habitatVal = $req?->habitat instanceof \App\Enums\PlantingHabitat
                        ? $req->habitat->value
                        : ((string) ($req?->habitat ?? 'terrestrial'));
                    $sRaw = (string)($req?->status ?? 'pending');
                    $sNormalized = str_replace([' ', '-'], '_', strtolower($sRaw));
                    $sLabels = [
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ];
                @endphp
                <tr class="{{ $isSub ? 'sub-row' : 'parent-row' }}">
                    <td class="left">
                        @if($isSub)
                            <span class="sub-arrow">&rarr;</span><span class="sub-no">{{ $req?->request_no ?? ('REQ-' . $req?->id) }}</span>
                        @else
                            <span class="req-no">{{ $req?->request_no ?? ('REQ-' . $req?->id) }}</span>
                        @endif
                    </td>
                    <td class="left">
                        <div style="{{ $isSub ? 'padding-left: 6px;' : '' }}">
                            <span style="font-weight: bold; color: {{ $isSub ? '#334155' : '#0f172a' }};">
                                {{ $req?->project_name ?? 'Untitled Project' }}
                            </span>
                            @if($isSub)
                                <span class="badge-sub">Sub-request</span>
                            @elseif($row->children_count > 0)
                                <span class="badge-parent">{{ $row->children_count }} {{ $row->children_count === 1 ? 'sub-request' : 'sub-requests' }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="left">{{ $partnerName }}</td>
                    <td class="left">{{ $locationName }}</td>
                    <td>{{ ucfirst($habitatVal) }}</td>
                    <td><strong>{{ number_format((int)($req?->target_trees ?? 0)) }}</strong></td>
                    <td>{{ optional($req?->request_date)->format('m/d/Y') ?? $req?->created_at?->format('m/d/Y') ?? '—' }}</td>
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
                    TOTAL CONSOLIDATED REPORT ({{ $totals['total_requests'] }} {{ $totals['total_requests'] === 1 ? 'Entry' : 'Entries' }} in Total: {{ $totals['parent_requests_count'] }} Main, {{ $totals['sub_requests_count'] }} Sub-request{{ $totals['sub_requests_count'] != 1 ? 's' : '' }})
                </td>
                <td>{{ number_format($totals['target_trees']) }}</td>
                <td colspan="2">{{ number_format($totals['total_requests']) }} Records</td>
            </tr>
        </tbody>
    </table>

    {{-- ========== SIGNATURES ========== --}}
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-container">
                    <div class="sig-role">Prepared By</div>
                    <div class="sig-line">
                        <div class="sig-name">MENRO Records Officer</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-container">
                    <div class="sig-role">Reviewed By</div>
                    <div class="sig-line">
                        <div class="sig-name">Forestry Section Head</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-container">
                    <div class="sig-role">Approved By</div>
                    <div class="sig-line">
                        <div class="sig-name">MENRO Officer</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>

