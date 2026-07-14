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

    // Calculate actual start/end dates for preset ranges so PDF headers are informative
    $today = date('Y-m-d');
    $presetDates = match ($range) {
        '7d'  => ['start' => date('Y-m-d', strtotime('-6 days')), 'end' => $today],
        '30d' => ['start' => date('Y-m-d', strtotime('-29 days')), 'end' => $today],
        '6m'  => ['start' => date('Y-m-d', strtotime('-6 months')), 'end' => $today],
        default => null,
    };

    // Short label for UI display
    $rangeLabel = match ($range) {
        '7d'  => '7 Hari Terakhir',
        '30d' => '30 Hari Terakhir',
        '6m'  => '6 Bulan Terakhir',
        'all' => 'Semua Waktu',
        'custom' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
    };

    // Full label with actual dates for PDF export headers
    $rangeLabelFull = match ($range) {
        '7d'  => '7 Hari Terakhir (' . date('d M Y', strtotime($presetDates['start'])) . ' - ' . date('d M Y', strtotime($presetDates['end'])) . ')',
        '30d' => '30 Hari Terakhir (' . date('d M Y', strtotime($presetDates['start'])) . ' - ' . date('d M Y', strtotime($presetDates['end'])) . ')',
        '6m'  => '6 Bulan Terakhir (' . date('d M Y', strtotime($presetDates['start'])) . ' - ' . date('d M Y', strtotime($presetDates['end'])) . ')',
        'all' => 'Semua Waktu',
        'custom' => date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate)),
    };

    return [
        'range' => $range,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'rangeLabel' => $rangeLabel,
        'rangeLabelFull' => $rangeLabelFull,
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
