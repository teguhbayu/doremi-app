<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

/**
 * Resolves the report page range filter from query params, including the
 * 'custom' start/end date mode. Falls back to $defaultRange when 'custom'
 * is requested without two valid, ordered dates.
 */
function resolveReportRangeFilter(array $query, string $defaultRange = '7d'): array
{
    $allowedRanges = ['7d', '30d', '6m', 'all', 'custom'];
    $range = in_array($query['range'] ?? '', $allowedRanges, true) ? $query['range'] : $defaultRange;

    $startDate = null;
    $endDate = null;

    if ($range === 'custom') {
        $start = (string) ($query['start_date'] ?? '');
        $end = (string) ($query['end_date'] ?? '');
        $startValid = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) && strtotime($start) !== false;
        $endValid = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) && strtotime($end) !== false;

        if ($startValid && $endValid && $start <= $end) {
            $startDate = $start;
            $endDate = $end;
        } else {
            $range = $defaultRange;
        }
    }

    $rangeLabel = match ($range) {
        '7d' => '7 Hari Terakhir',
        '30d' => '30 Hari Terakhir',
        '6m' => '6 Bulan Terakhir',
        'all' => 'Semua Waktu',
        'custom' => date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)),
    };

    return [
        'range' => $range,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'rangeLabel' => $rangeLabel,
    ];
}

/** Builds the (Y-m-d => 'd M') label map for a custom range spanning <= 31 days. */
function reportCustomDailyLabels(string $startDate, string $endDate): array
{
    $labels = [];
    $cursor = new DateTime($startDate);
    $end = new DateTime($endDate);
    while ($cursor <= $end) {
        $labels[$cursor->format('Y-m-d')] = $cursor->format('d M');
        $cursor->modify('+1 day');
    }
    return $labels;
}

/** Builds the (Y-m => 'M Y') label map for a custom range spanning > 31 days. */
function reportCustomMonthlyLabels(string $startDate, string $endDate): array
{
    $labels = [];
    $cursor = new DateTime((new DateTime($startDate))->format('Y-m-01'));
    $endMonth = new DateTime((new DateTime($endDate))->format('Y-m-01'));
    while ($cursor <= $endMonth) {
        $labels[$cursor->format('Y-m')] = $cursor->format('M Y');
        $cursor->modify('+1 month');
    }
    return $labels;
}

/** True when a custom range should use daily trend granularity (<= 31 days). */
function reportCustomRangeIsDaily(string $startDate, string $endDate): bool
{
    $span = (int) (new DateTime($startDate))->diff(new DateTime($endDate))->days + 1;
    return $span <= 31;
}
