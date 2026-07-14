# One-time exporter: dumps every sheet of the legacy planting workbook to CSV
# so `php artisan import:legacy-planting` can parse them with native fgetcsv.
# Requires Microsoft Excel (COM automation). Close the workbook in Excel first.
param(
    [string]$Workbook = "C:\Users\Hp\Downloads\2024-Tree-Planting-Data.xlsx",
    [string]$OutDir = (Join-Path $PSScriptRoot "..\..\storage\app\imports\legacy-planting"),
    [int]$MaxCols = 15
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path $Workbook)) {
    throw "Workbook not found: $Workbook"
}
New-Item -ItemType Directory -Force $OutDir | Out-Null
$OutDir = (Resolve-Path $OutDir).Path

function Convert-Cell($cell) {
    $v = $cell.Value2
    if ($null -eq $v) { return "" }
    if ($v -is [double]) {
        # Date-formatted numeric cells -> ISO date, so PHP never sees "17-Oct-24" etc.
        $fmt = $cell.NumberFormat
        if ($fmt -is [string] -and $fmt -match '[dmy]' -and $fmt -notmatch '(?i)general|#|0\.|%') {
            try { return [DateTime]::FromOADate($v).ToString('yyyy-MM-dd') } catch { }
        }
        return $v.ToString([System.Globalization.CultureInfo]::InvariantCulture)
    }
    return [string]$v
}

function Csv-Quote([string]$s) {
    return '"' + ($s -replace '"', '""' -replace "[\r\n]+", ' ') + '"'
}

$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$wb = $null
try {
    $wb = $excel.Workbooks.Open($Workbook, 0, $true)  # read-only
    foreach ($ws in $wb.Worksheets) {
        $used = $ws.UsedRange
        $firstRow = $used.Row
        $rows = $used.Rows.Count
        $cols = [Math]::Min($used.Columns.Count, $MaxCols)

        $safe = ($ws.Name -replace '[^A-Za-z0-9]+', '-').Trim('-')
        $outFile = Join-Path $OutDir "$safe.csv"
        $lines = New-Object System.Collections.Generic.List[string]

        for ($r = 1; $r -le $rows; $r++) {
            $excelRow = $firstRow + $r - 1
            $vals = @(Csv-Quote "$excelRow")  # __row column for issue reports
            for ($c = 1; $c -le $cols; $c++) {
                $vals += Csv-Quote (Convert-Cell $used.Cells.Item($r, $c))
            }
            $lines.Add(($vals -join ','))
        }
        [System.IO.File]::WriteAllLines($outFile, $lines, (New-Object System.Text.UTF8Encoding($false)))
        Write-Output ("exported {0,-20} rows={1,-4} -> {2}" -f $ws.Name, $rows, $outFile)
    }
} finally {
    if ($wb) { $wb.Close($false) }
    $excel.Quit()
    [System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
Write-Output "done"
