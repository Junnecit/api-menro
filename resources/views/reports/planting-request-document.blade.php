<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting Request Document - {{ $request->request_no }}</title>
    <style>
        @page {
            margin: 24px 32px 28px 32px;
            size: portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.4;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* --- HEADER --- */
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
            width: 65px;
            text-align: center;
        }
        .seal-img {
            width: 55px;
            height: 55px;
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
        }
        .agency-name {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.3px;
        }
        .agency-office {
            font-size: 13px;
            font-weight: bold;
            color: #064e3b;
            margin-top: 1px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .agency-sub {
            font-size: 8px;
            color: #059669;
            font-style: italic;
        }

        /* --- DOC TITLE BAR --- */
        .doc-title-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .doc-title-bar td {
            vertical-align: middle;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .doc-subtitle {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 12px;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        .status-approved { background-color: #059669; }
        .status-pending { background-color: #d97706; }
        .status-in_progress { background-color: #0284c7; }
        .status-completed { background-color: #15803d; }
        .status-rejected { background-color: #dc2626; }

        /* --- META BAR --- */
        .meta-bar {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-left: 4px solid #059669;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 8.5px;
            color: #475569;
            border-radius: 3px;
        }
        .meta-bar strong { color: #0f172a; font-weight: bold; }

        /* --- SECTION BOXES --- */
        .section-box {
            margin-bottom: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background-color: #ffffff;
        }
        .section-header {
            background-color: #f1f5f9;
            color: #064e3b;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 8px;
            border-bottom: 1px solid #cbd5e1;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .section-content {
            padding: 8px;
        }

        /* --- GRID TABLES --- */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 22%;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .info-value {
            color: #0f172a;
            font-size: 9px;
        }
        .info-value.highlight {
            font-weight: bold;
            color: #064e3b;
            font-size: 10px;
        }

        /* --- DATA TABLE --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 4px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: center;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #064e3b;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table td.left {
            text-align: left;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* --- SIGNATURES --- */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .sig-table td {
            width: 33.33%;
            padding: 0 8px;
            vertical-align: top;
            text-align: center;
        }
        .sig-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 6px;
            background-color: #f8fafc;
        }
        .sig-role {
            font-size: 8.5px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            margin-bottom: 35px;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .sig-line {
            border-top: 1px solid #1e293b;
            margin: 0 6px;
            padding-top: 4px;
        }
        .sig-name {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1px;
        }

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
            font-size: 7.5px;
            color: #64748b;
        }
        .footer-table td.right { text-align: right; }
    </style>
</head>
<body>

    <footer>
        <table class="footer-table">
            <tr>
                <td class="left">MENRO Tagoloan &bull; Official Planting Request Record &bull; Ref: {{ $request->request_no ?? 'REQ-' . $request->id }}</td>
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
                        @if(!empty($provinceSealDataUri))
                            <img src="{{ $provinceSealDataUri }}" alt="Province Seal" class="seal-img">
                        @endif
                    </td>
                    <td class="agency-td">
                        <div class="agency-republic">Republic of the Philippines</div>
                        <div class="agency-name">PROVINCE OF MISAMIS ORIENTAL</div>
                        <div class="agency-name">MUNICIPALITY OF TAGOLOAN</div>
                        <div class="agency-office">Municipal Environment and Natural Resources Office</div>
                        <div class="agency-sub">Tree Planting &amp; Environmental Stewardship Program</div>
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

    {{-- ========== DOCUMENT TITLE & STATUS BAR ========== --}}
    <table class="doc-title-bar">
        <tr>
            <td>
                <div class="doc-title">Planting Request Summary Document</div>
                <div class="doc-subtitle">Official Filing &amp; Allocation Record &bull; Municipality of Tagoloan</div>
            </td>
            <td style="text-align: right; width: 140px;">
                @php
                    $statusKey = strtolower($request->status ?? 'pending');
                    $statusLabels = [
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ];
                @endphp
                <span class="status-badge status-{{ $statusKey }}">
                    {{ $statusLabels[$statusKey] ?? ucfirst($statusKey) }}
                </span>
            </td>
        </tr>
    </table>

    <div class="meta-bar">
        Request Reference: <strong>{{ $request->request_no ?? 'REQ-' . $request->id }}</strong> &nbsp;|&nbsp;
        Date Filed: <strong>{{ optional($request->request_date)->format('F d, Y') ?? $request->created_at->format('F d, Y') }}</strong> &nbsp;|&nbsp;
        Generated: <strong>{{ ($generatedAt ?? now())->format('M d, Y g:i A') }}</strong>
    </div>

    {{-- ========== SECTION 1: REQUESTER & PROJECT DETAILS ========== --}}
    <div class="section-box">
        <div class="section-header">1. Project &amp; Requester Information</div>
        <div class="section-content">
            <table class="info-table">
                <tr>
                    <td class="info-label">Project Title:</td>
                    <td class="info-value highlight" colspan="3">{{ $request->project_name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Partner Agency / Org:</td>
                    <td class="info-value" style="width: 38%;">
                        <strong>{{ $request->agency?->name ?? 'Direct Community Request' }}</strong>
                    </td>
                    <td class="info-label" style="width: 18%;">Requester Name:</td>
                    <td class="info-value" style="width: 26%;">
                        {{ $request->requester_name ?? $request->user?->name ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Planting Habitat:</td>
                    <td class="info-value">
                        <strong>{{ ucfirst($request->habitat?->value ?? 'Terrestrial') }}</strong>
                    </td>
                    <td class="info-label">Target Trees:</td>
                    <td class="info-value highlight">
                        {{ number_format((int)$request->target_trees) }} Seedlings
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Submitted By:</td>
                    <td class="info-value">
                        {{ $request->user?->name ?? 'System User' }} ({{ $request->user?->email ?? '-' }})
                    </td>
                    <td class="info-label">Attached Document:</td>
                    <td class="info-value">
                        {{ $request->document_name ?? 'Digital Submission' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 2: LOCATION & SITE INFORMATION ========== --}}
    <div class="section-box">
        <div class="section-header">2. Location &amp; Planting Site Information</div>
        <div class="section-content">
            <table class="info-table">
                <tr>
                    <td class="info-label">Barangay:</td>
                    <td class="info-value highlight" style="width: 38%;">
                        {{ $barangayName ?? $request->location ?? 'Tagoloan' }}
                    </td>
                    <td class="info-label" style="width: 18%;">Municipality / Prov:</td>
                    <td class="info-value" style="width: 26%;">
                        Tagoloan, Misamis Oriental
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Specific Site Address:</td>
                    <td class="info-value" colspan="3">
                        {{ $request->custom_address ?? $request->location ?? 'Tagoloan Planting Site' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 3: SEEDLING SPECIES BREAKDOWN ========== --}}
    <div class="section-box">
        <div class="section-header">3. Seedling Species Allocation</div>
        <div class="section-content" style="padding: 4px 6px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">No.</th>
                        <th class="left" style="width: 44%;">Species Name / Variety</th>
                        <th style="width: 24%;">Quantity Target</th>
                        <th class="left" style="width: 24%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $speciesDraft = $request->seedling_draft;
                        $speciesList = [];
                        if (is_array($speciesDraft) && !empty($speciesDraft['species'])) {
                            $speciesList = $speciesDraft['species'];
                        } elseif (!empty($request->seedling_draft_raw)) {
                            $speciesList = [$request->seedling_draft_raw];
                        }
                    @endphp

                    @if(!empty($speciesList))
                        @foreach($speciesList as $idx => $speciesName)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="left"><strong>{{ is_array($speciesName) ? ($speciesName['name'] ?? $speciesName['species'] ?? 'Tree Seedlings') : $speciesName }}</strong></td>
                                <td>
                                    {{ is_array($speciesName) && !empty($speciesName['qty']) ? number_format((int)$speciesName['qty']) : number_format((int)$request->target_trees) }}
                                </td>
                                <td class="left">{{ is_array($speciesName) ? ($speciesName['notes'] ?? 'Allocated') : 'Allocated for project' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>1</td>
                            <td class="left"><strong>Native Forest &amp; Mangrove Species</strong></td>
                            <td>{{ number_format((int)$request->target_trees) }}</td>
                            <td class="left">Allocated for Tagoloan greening project</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 4: OFFICIAL ACTIONS & SIGNATURES ========== --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-card">
                    <div class="sig-role">1. Requester / Applicant</div>
                    <div class="sig-line">
                        <div class="sig-name">{{ $request->requester_name ?? $request->agency?->name ?? 'Applicant' }}</div>
                        <div class="sig-title">Signature over Printed Name</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-card">
                    <div class="sig-role">2. MENRO Field Verifier</div>
                    <div class="sig-line">
                        <div class="sig-name">MENRO Site Officer</div>
                        <div class="sig-title">Site Assessment &amp; Validation</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-card">
                    <div class="sig-role">3. Approved &amp; Released By</div>
                    <div class="sig-line">
                        <div class="sig-name">MENRO Officer</div>
                        <div class="sig-title">Municipal Environment &amp; Natural Resources</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
