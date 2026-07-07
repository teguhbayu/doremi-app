<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function dbBindParams(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types === '') {
        return;
    }

    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }

    mysqli_stmt_bind_param($stmt, $types, ...$refs);
}

function dbFetchAll(mysqli $db, string $sql, string $types = '', array $params = []): array
{
    if ($types === '') {
        $result = mysqli_query($db, $sql);
        if (!$result) {
            throw new RuntimeException(mysqli_error($db));
        }

        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        dbClearStoredResults($db);

        return $rows;
    }

    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        throw new RuntimeException(mysqli_error($db));
    }

    dbBindParams($stmt, $types, $params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    dbClearStoredResults($db);

    return $rows;
}

function dbFetchOne(mysqli $db, string $sql, string $types = '', array $params = []): ?array
{
    $rows = dbFetchAll($db, $sql, $types, $params);
    return $rows[0] ?? null;
}

function dbFetchValue(mysqli $db, string $sql, string $types = '', array $params = [], string $column = 'total'): mixed
{
    $row = dbFetchOne($db, $sql, $types, $params);
    return $row[$column] ?? null;
}

function dbExecute(mysqli $db, string $sql, string $types = '', array $params = []): int
{
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) {
        throw new RuntimeException(mysqli_error($db));
    }

    dbBindParams($stmt, $types, $params);
    mysqli_stmt_execute($stmt);
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    dbClearStoredResults($db);

    return $affectedRows;
}

function dbClearStoredResults(mysqli $db): void
{
    while (mysqli_more_results($db)) {
        mysqli_next_result($db);
        $result = mysqli_store_result($db);
        if ($result) {
            mysqli_free_result($result);
        }
    }
}
