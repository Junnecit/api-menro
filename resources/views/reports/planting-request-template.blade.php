<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting Request Form - Official Template</title>
    <style>
        @page {
            margin: 22px 30px 24px 30px;
            size: portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.35;
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

        /* --- DOC TITLE & CONTROL BAR --- */
        .doc-title-bar {
            background-color: #064e3b;
            color: #ffffff;
            text-align: center;
            padding: 6px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
        }
        .doc-title {
            font-size: 12.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 8px;
            color: #a7f3d0;
            margin-top: 2px;
            letter-spacing: 0.4px;
        }

        .control-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8px;
            color: #475569;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
        }
        .control-bar td {
            padding: 3px 6px;
        }
        .control-bar td.right {
            text-align: right;
        }

        /* --- SECTIONS --- */
        .section-box {
            margin-bottom: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background-color: #ffffff;
        }
        .section-header {
            background-color: #f1f5f9;
            color: #064e3b;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3.5px 7px;
            border-bottom: 1px solid #cbd5e1;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .section-content {
            padding: 6px 8px;
        }

        /* --- FORM FIELDS & UNDERLINES --- */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .form-table td {
            padding: 3.5px 4px;
            vertical-align: middle;
        }
        .field-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #334155;
            white-space: nowrap;
        }
        .field-underline {
            border-bottom: 1px solid #64748b;
            color: #0f172a;
            font-size: 9px;
            min-height: 15px;
            padding-bottom: 1px;
            padding-left: 4px;
        }
        .field-underline-double {
            border-bottom: 1px solid #64748b;
            min-height: 14px;
            margin-top: 5px;
        }

        /* --- CHECKBOXES --- */
        .check-item {
            display: inline-block;
            margin-right: 14px;
            font-size: 8.5px;
            color: #1e293b;
            vertical-align: middle;
        }
        .checkbox-sq {
            display: inline-block;
            width: 10.5px;
            height: 10.5px;
            border: 1.2px solid #064e3b;
            margin-right: 4px;
            vertical-align: middle;
            background-color: #ffffff;
            text-align: center;
            line-height: 9px;
            font-size: 8px;
            font-weight: bold;
            color: #064e3b;
        }

        /* --- SPECIES TABLE --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 1px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            text-align: center;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #064e3b;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table td.left {
            text-align: left;
            padding-left: 6px;
        }
        .data-table td.blank-row {
            height: 18px;
        }

        /* --- COMMITMENT LIST --- */
        .agreement-list {
            margin: 0;
            padding-left: 15px;
            font-size: 8px;
            color: #334155;
            line-height: 1.35;
        }
        .agreement-list li {
            margin-bottom: 2px;
        }

        /* --- SIGNATURES --- */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .sig-table td {
            width: 25%;
            padding: 0 4px;
            vertical-align: top;
            text-align: center;
        }
        .sig-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 4px;
            background-color: #f8fafc;
        }
        .sig-role {
            font-size: 8px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            margin-bottom: 24px;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .sig-line {
            border-top: 1px solid #1e293b;
            margin: 0 4px;
            padding-top: 3px;
        }
        .sig-name {
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1px;
        }

        /* --- INSTRUCTION FOOTER --- */
        .notice-box {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-left: 3px solid #059669;
            border-radius: 3px;
            padding: 4px 7px;
            margin-top: 6px;
            font-size: 7.5px;
            color: #065f46;
        }
        .notice-box strong {
            color: #064e3b;
        }
        footer {
            position: fixed;
            bottom: -18px;
            left: 0;
            right: 0;
            height: 16px;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
        }
        .page-footer {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            color: #64748b;
        }
        .page-footer td.right {
            text-align: right;
            padding-right: 65px;
        }
    </style>
</head>
<body>

    <footer>
        <table class="page-footer">
            <tr>
                <td>Republic of the Philippines &bull; Province of Misamis Oriental &bull; Municipality of Tagoloan &bull; MENRO Official Form</td>
                <td class="right">Doc Ref: MENRO-PR-FORM-2026</td>
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

    {{-- ========== FORM TITLE & CONTROL BAR ========== --}}
    <div class="doc-title-bar">
        <div class="doc-title">Planting Request &amp; Seedling Allocation Application</div>
        <div class="doc-subtitle">Official Form for Community, Institutional, and Environmental Tree Planting Projects</div>
    </div>

    <table class="control-bar">
        <tr>
            <td><strong>Form Control No:</strong> MENRO-TAG-PR-2026</td>
            <td><strong>Revision No:</strong> 02</td>
            <td><strong>Date Issued:</strong> {{ now()->format('F Y') }}</td>
            <td class="right"><strong>Target Municipality:</strong> Tagoloan, Misamis Oriental</td>
        </tr>
    </table>

    {{-- ========== SECTION 1: APPLICANT / REQUESTER INFORMATION ========== --}}
    <div class="section-box">
        <div class="section-header">Section 1: Applicant &amp; Organization Information</div>
        <div class="section-content">
            <table class="form-table">
                <tr>
                    <td class="field-label" style="width: 22%;">Name of Requester / Head:</td>
                    <td style="width: 44%;">
                        <div class="field-underline">{{ $fields['requester_name'] ?? '' }}</div>
                    </td>
                    <td class="field-label" style="width: 14%;">Date of Request:</td>
                    <td style="width: 20%;">
                        <div class="field-underline">{{ isset($fields['request_date']) ? \Carbon\Carbon::parse($fields['request_date'])->format('m/d/Y') : '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Agency / Organization:</td>
                    <td>
                        <div class="field-underline">{{ $fields['agency_name'] ?? '' }}</div>
                    </td>
                    <td class="field-label">Contact Number:</td>
                    <td>
                        <div class="field-underline">{{ $fields['contact_number'] ?? '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Designation / Position:</td>
                    <td>
                        <div class="field-underline">{{ $fields['position'] ?? '' }}</div>
                    </td>
                    <td class="field-label">Email Address:</td>
                    <td>
                        <div class="field-underline">{{ $fields['email'] ?? '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Requester Category:</td>
                    <td colspan="3" style="padding-top: 4px;">
                        <span class="check-item"><span class="checkbox-sq"></span> Individual / Family</span>
                        <span class="check-item"><span class="checkbox-sq"></span> LGU / Barangay</span>
                        <span class="check-item"><span class="checkbox-sq"></span> NGO / Civil Society</span>
                        <span class="check-item"><span class="checkbox-sq"></span> School / University</span>
                        <span class="check-item"><span class="checkbox-sq"></span> Corporate / Enterprise</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 2: PLANTING PROJECT SPECIFICATIONS ========== --}}
    <div class="section-box">
        <div class="section-header">Section 2: Planting Project Specifications</div>
        <div class="section-content">
            <table class="form-table">
                <tr>
                    <td class="field-label" style="width: 22%;">Project Name / Title:</td>
                    <td colspan="3">
                        <div class="field-underline" style="font-weight: bold; color: #064e3b;">
                            {{ $fields['project_name'] ?? '' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Target Number of Trees:</td>
                    <td style="width: 38%;">
                        <div class="field-underline">
                            <strong>{{ !empty($fields['target_trees']) ? number_format((int)$fields['target_trees']) . ' seedlings' : '' }}</strong>
                        </div>
                    </td>
                    <td class="field-label" style="width: 18%;">Proposed Planting Date:</td>
                    <td style="width: 22%;">
                        <div class="field-underline">{{ $fields['target_date'] ?? '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Planting Habitat / Zone:</td>
                    <td colspan="3" style="padding-top: 4px; padding-bottom: 4px;">
                        @php
                            $selectedHabitat = strtolower($fields['habitat'] ?? '');
                        @endphp
                        <span class="check-item">
                            <span class="checkbox-sq">{{ $selectedHabitat === 'terrestrial' ? '✓' : '' }}</span> Terrestrial Forest / Upland
                        </span>
                        <span class="check-item">
                            <span class="checkbox-sq">{{ ($selectedHabitat === 'mangrove' || $selectedHabitat === 'coastal') ? '✓' : '' }}</span> Mangrove / Coastal
                        </span>
                        <span class="check-item">
                            <span class="checkbox-sq">{{ $selectedHabitat === 'riparian' ? '✓' : '' }}</span> Riparian / Riverbank
                        </span>
                        <span class="check-item">
                            <span class="checkbox-sq">{{ $selectedHabitat === 'urban' ? '✓' : '' }}</span> Urban / School / Park
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="field-label" style="vertical-align: top; padding-top: 5px;">Notes / Purpose:</td>
                    <td colspan="3">
                        <div class="field-underline">{{ $fields['notes'] ?? '' }}</div>
                        <div class="field-underline-double"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 3: PLANTING SITE & LOCATION DETAILS ========== --}}
    <div class="section-box">
        <div class="section-header">Section 3: Planting Site &amp; Location Details</div>
        <div class="section-content">
            <table class="form-table">
                <tr>
                    <td class="field-label" style="width: 22%;">Barangay (Tagoloan):</td>
                    <td style="width: 38%;">
                        <div class="field-underline">
                            <strong>{{ $fields['barangay'] ?? '' }}</strong>
                        </div>
                    </td>
                    <td class="field-label" style="width: 18%;">Municipality &amp; Province:</td>
                    <td style="width: 22%;">
                        <div class="field-underline" style="color: #064e3b; font-weight: bold;">Tagoloan, Misamis Oriental</div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Sitio / Street / Zone:</td>
                    <td>
                        <div class="field-underline">{{ $fields['custom_address'] ?? '' }}</div>
                    </td>
                    <td class="field-label">Area Size (ha / sqm):</td>
                    <td>
                        <div class="field-underline">{{ $fields['area_size'] ?? '' }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="field-label">Land Ownership:</td>
                    <td colspan="3" style="padding-top: 4px;">
                        <span class="check-item"><span class="checkbox-sq"></span> Public / Government Land</span>
                        <span class="check-item"><span class="checkbox-sq"></span> Community / Barangay Domain</span>
                        <span class="check-item"><span class="checkbox-sq"></span> School / Institutional Ground</span>
                        <span class="check-item"><span class="checkbox-sq"></span> Private Land (with consent)</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 4: SEEDLING SPECIES & ALLOCATION TABLE ========== --}}
    <div class="section-box">
        <div class="section-header">Section 4: Seedling Species Allocation Breakdown</div>
        <div class="section-content" style="padding: 4px 6px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">#</th>
                        <th class="left" style="width: 40%;">Seedling Species Requested (e.g. Narra, Mahogany, Bakauan, Molave, Agoho)</th>
                        <th style="width: 16%;">Qty Requested</th>
                        <th style="width: 16%;">Approved Qty</th>
                        <th class="left" style="width: 22%;">Remarks / Spacing</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $speciesList = $fields['species_items'] ?? [];
                        if (empty($speciesList) && !empty($fields['seedling_type'])) {
                            $speciesList = [
                                ['species' => $fields['seedling_type'], 'qty' => $fields['target_trees'] ?? '']
                            ];
                        }
                    @endphp

                    @for($i = 0; $i < 4; $i++)
                        @php $item = $speciesList[$i] ?? null; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="left font-semibold">{{ $item['species'] ?? '' }}</td>
                            <td>{{ !empty($item['qty']) ? number_format((int)$item['qty']) : '' }}</td>
                            <td>{{ !empty($item['approved_qty']) ? number_format((int)$item['approved_qty']) : '' }}</td>
                            <td class="left">{{ $item['remarks'] ?? '' }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== SECTION 5: POST-PLANTING CARE & MONITORING COMMITMENT ========== --}}
    <div class="section-box">
        <div class="section-header">Section 5: Post-Planting Care &amp; Monitoring Agreement</div>
        <div class="section-content" style="padding: 4px 8px;">
            <ul class="agreement-list">
                <li><strong>Maintenance Commitment:</strong> The requesting party agrees to conduct regular weeding, watering, and protective fencing for the planted seedlings for a minimum of twelve (12) months.</li>
                <li><strong>Mortality Monitoring:</strong> Seedling survival status and mortality counts must be documented and reported to MENRO Tagoloan for replanting coordination.</li>
                <li><strong>Periodic Inspection:</strong> The site shall be accessible for scheduled field validation by MENRO Forestry &amp; Monitoring technical inspectors.</li>
                <li><strong>Digital Submission:</strong> For online filing, this document or filled photo should be uploaded to the official MENRO Tree Monitoring Portal.</li>
            </ul>
        </div>
    </div>

    {{-- ========== SECTION 6: SIGNATURES & OFFICIAL APPROVALS ========== --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-card">
                    <div class="sig-role">1. Prepared &amp; Requested By</div>
                    <div class="sig-line">
                        <div class="sig-name">{{ $fields['requester_name'] ?? 'Applicant Signature' }}</div>
                        <div class="sig-title">Signature over Printed Name</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-card">
                    <div class="sig-role">2. Endorsed / Recommended</div>
                    <div class="sig-line">
                        <div class="sig-name">Barangay Captain / Head</div>
                        <div class="sig-title">Endorsing Official Signature</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-card">
                    <div class="sig-role">3. Verified &amp; Inspected</div>
                    <div class="sig-line">
                        <div class="sig-name">Technical Inspector</div>
                        <div class="sig-title">MENRO Forestry Unit</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-card">
                    <div class="sig-role">4. Approved By</div>
                    <div class="sig-line">
                        <div class="sig-name">MENRO Officer</div>
                        <div class="sig-title">Municipal Environment &amp; Natural Resources</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ========== INSTRUCTION & FOOTER ========== --}}
    <div class="notice-box">
        <strong>Submission Guidelines:</strong> Fill out the underlines above or print and complete by hand. For an editable digital Word version, download the official DOCX template. Submit completed applications to <em>MENRO Tagoloan, Municipal Hall, Tagoloan, Misamis Oriental</em> or upload via the <em>Tree Monitoring Web Portal</em>.
    </div>

</body>
</html>
