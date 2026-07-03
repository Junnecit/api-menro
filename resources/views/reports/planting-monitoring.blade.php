<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MENRO Planting &amp; Monitoring Report</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1f2937; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; padding: 0; }
        .seal { width: 80px; text-align: center; }
        .seal img { width: 64px; height: 64px; border-radius: 50%; }
        .seal-placeholder {
            display: inline-block; width: 60px; height: 60px; border-radius: 50%;
            border: 2px dashed #94a3b8;
        }
        .agency-block { text-align: center; }
        .agency-block .republic { font-size: 10px; }
        .agency-block .province,
        .agency-block .municipality { font-size: 11px; font-weight: bold; }
        /*
         * The original sheet uses a blackletter/old-English display font for
         * "Municipal Environment and Natural Resources Office". No matching font
         * file exists in this repo, and we will not fabricate/download one.
         * Substituting a bold serif (dompdf built-in Times) as the closest safe
         * approximation until a licensed font file is supplied.
         */
        .agency-block .office-title {
            font-family: 'Times New Roman', Georgia, serif;
            font-weight: bold;
            font-size: 15px;
            margin-top: 2px;
        }
        .banner {
            /* dompdf does not reliably support CSS linear-gradient() across its
             * renderer versions, so we use a solid fallback color approximating
             * the screenshot's purple/violet banner instead of a gradient. */
            background-color: #6d28d9;
            color: #ffffff;
            text-align: center;
            padding: 6px 0;
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
        }
        table.report { width: 100%; table-layout: fixed; border-collapse: collapse; margin-top: 12px; }
        table.report th, table.report td {
            border: 1px solid #94a3b8; padding: 3px 4px; font-size: 7px;
            word-wrap: break-word; overflow-wrap: break-word;
        }
        table.report thead th { background-color: #dbeafe; text-align: center; font-weight: bold; }
        table.report td { text-align: center; }
        table.report td.left { text-align: left; }
        tr.total-row td { background-color: #fef08a; font-weight: bold; }
        .meta { margin-top: 6px; font-size: 8px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="seal">
                    {{-- Falls back to a neutral placeholder if the seal file is ever missing. --}}
                    @if($provinceSealDataUri)
                        <img src="{{ $provinceSealDataUri }}" alt="Province Seal">
                    @else
                        <span class="seal-placeholder"></span>
                    @endif
                </td>
                <td class="agency-block">
                    <div class="republic">Republic of the Philippines</div>
                    <div class="province">PROVINCE OF MISAMIS ORIENTAL</div>
                    <div class="municipality">MUNICIPALITY OF TAGOLOAN</div>
                    <div class="office-title">Municipal Environment and Natural Resources Office</div>
                </td>
                <td class="seal">
                    @if($menroSealDataUri)
                        <img src="{{ $menroSealDataUri }}" alt="MENRO Seal">
                    @else
                        <span class="seal-placeholder"></span>
                    @endif
                </td>
            </tr>
        </table>
        <div class="banner">PLANTING &amp; MONITORING REPORT</div>
    </div>

    <table class="report">
        <colgroup>
            <col style="width: 9%">
            <col style="width: 17%">
            <col style="width: 14%">
            <col style="width: 10%">
            <col style="width: 9%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 9%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Date Planted</th>
                <th rowspan="2">Name of Person Request for Planting or Agency</th>
                <th rowspan="2">Area Planted</th>
                <th rowspan="2">Type of Seedling Planted</th>
                <th colspan="6">Monitoring</th>
            </tr>
            <tr>
                <th>Date Monitoring</th>
                <th>No. of Seedling Planted</th>
                <th>No. of Re-plant</th>
                <th>No. of Plant Survive</th>
                <th>No. of Plant Died</th>
                <th>Percentage of Survival Rate (%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ optional($record->request?->request_date)->format('m/d/Y') }}</td>
                    <td class="left">{{ $record->request?->agency?->name ?? $record->request?->requester_name ?? '—' }}</td>
                    <td class="left">{{ $record->request?->location }}</td>
                    <td class="left">{{ $record->seedling_type }}</td>
                    <td>{{ optional($record->date_monitoring)->format('m/d/Y') }}</td>
                    <td>{{ $record->seedlings_planted }}</td>
                    <td>{{ $record->replanted_count }}</td>
                    <td>{{ $record->survived_count }}</td>
                    <td>{{ $record->died_count }}</td>
                    <td>
                        {{ $record->seedlings_planted > 0
                            ? number_format($record->survived_count / $record->seedlings_planted * 100, 2)
                            : '0.00' }}%
                    </td>
                </tr>
            @empty
                <tr><td colspan="10">No monitoring records match the selected filters.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5">Total</td>
                <td>{{ $totals['seedlings_planted'] }}</td>
                <td>{{ $totals['replanted_count'] }}</td>
                <td>{{ $totals['survived_count'] }}</td>
                <td>{{ $totals['died_count'] }}</td>
                <td>{{ number_format($totals['survival_rate'], 2) }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="meta">Generated {{ $generatedAt->format('M d, Y g:i A') }}</div>
</body>
</html>
