$path = "database\database.sql"
$raw = Get-Content $path -Raw
function ExtractTuples($raw, $table) {
    $pattern = "INSERT INTO `" + $table + "`.*?VALUES(.*?);"
    $m = [regex]::Match($raw, $pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
    if (-not $m.Success) { return @() }
    $block = $m.Groups[1].Value.Trim()
    $block = $block -replace "\)\s*,\s*\r?\n\s*\(", ")###("
    $parts = $block -split "###"
    return $parts
}

$instTuples = ExtractTuples $raw 'istituti_e_partner'
$totalInst = $instTuples.Count
$instIDs = @()
$instWith1 = 0
foreach ($t in $instTuples) {
    $s = $t.Trim()
    if ($s.StartsWith('(')) { $s = $s.Substring(1) }
    if ($s.EndsWith('),')) { $s = $s.Substring(0,$s.Length-2) } elseif ($s.EndsWith(')')) { $s = $s.Substring(0,$s.Length-1) }
    if ($s -match '^\s*([0-9]+)\s*,') { $instIDs += [int]$matches[1] }
    if ($s -match ',\s*1\s*$') { $instWith1++ }
}

$attTuples = ExtractTuples $raw 'attivita'
$attTotal = $attTuples.Count
$refIDs = @()
foreach ($t in $attTuples) {
    $s = $t.Trim()
    if ($s.StartsWith('(')) { $s = $s.Substring(1) }
    if ($s.EndsWith('),')) { $s = $s.Substring(0,$s.Length-2) } elseif ($s.EndsWith(')')) { $s = $s.Substring(0,$s.Length-1) }
    $parts = $s -split ","
    if ($parts.Length -ge 2) {
        $id = $parts[1].Trim()
        $id = $id -replace "^'|'$",""
        if ($id -match '^\d+$') { $refIDs += [int]$id }
    }
}

$instIDsSet = $instIDs | Sort-Object -Unique
$refIDsSet = $refIDs | Sort-Object -Unique
$missing = $refIDsSet | Where-Object {$_ -notin $instIDsSet}

Write-Output "istituti_e_partner tuples: $totalInst"
Write-Output "istituti_e_partner with Stato_Validazione=1: $instWith1"
Write-Output "distinct istituti_e_partner IDs: $($instIDsSet.Count)"
Write-Output "attivita tuples: $attTotal"
Write-Output "distinct referenced istituto_ids in attivita: $($refIDsSet.Count)"
if ($missing.Count -gt 0) {
    Write-Output "Referenced istituto_ids not present in istituti_e_partner:"
    $missing | ForEach-Object { Write-Output $_ }
} else {
    Write-Output "All attivita.istituto_id reference existing istituti_e_partner IDs"
}
