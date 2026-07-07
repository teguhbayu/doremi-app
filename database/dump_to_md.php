<?php
/**
 * Dump all table data from the doremi database into a Markdown file.
 * Usage: php dump_to_md.php
 * Output: database/database_dump.md
 */

// Load .env and connect
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

$host     = $_ENV["DB_HOST"] ?? '127.0.0.1';
$port     = (int) ($_ENV["DB_PORT"] ?? '3306');
$user     = $_ENV["DB_USER"];
$pass     = $_ENV["DB_PASS"];
$database = $_ENV["DB_DATABASE"];

$db = mysqli_connect($host, $user, $pass, $database, $port);
if (!$db || mysqli_connect_error()) {
    fwrite(STDERR, "Database connection failed: " . mysqli_connect_error() . "\n");
    exit(1);
}

mysqli_set_charset($db, 'utf8mb4');

// Get all table names
$tablesResult = mysqli_query($db, "SHOW TABLES");
if (!$tablesResult) {
    fwrite(STDERR, "Failed to list tables: " . mysqli_error($db) . "\n");
    exit(1);
}

$tables = [];
while ($row = mysqli_fetch_row($tablesResult)) {
    $tables[] = $row[0];
}
mysqli_free_result($tablesResult);

// Build markdown
$md = "# Database Dump — `{$database}`\n\n";
$md .= "> Generated on " . date('Y-m-d H:i:s T') . "\n\n";
$md .= "---\n\n";

$md .= "## Table of Contents\n\n";
foreach ($tables as $i => $table) {
    $num = $i + 1;
    $md .= "{$num}. [{$table}](#{$table})\n";
}
$md .= "\n---\n\n";

foreach ($tables as $table) {
    $escapedTable = mysqli_real_escape_string($db, $table);
    
    // Count rows
    $countResult = mysqli_query($db, "SELECT COUNT(*) AS cnt FROM `{$escapedTable}`");
    $countRow = mysqli_fetch_assoc($countResult);
    $rowCount = $countRow['cnt'];
    mysqli_free_result($countResult);
    
    $md .= "## {$table}\n\n";
    $md .= "**Rows:** {$rowCount}\n\n";
    
    if ($rowCount == 0) {
        $md .= "_No data._\n\n---\n\n";
        continue;
    }
    
    // Fetch all rows
    $dataResult = mysqli_query($db, "SELECT * FROM `{$escapedTable}`");
    if (!$dataResult) {
        $md .= "_Error querying table: " . mysqli_error($db) . "_\n\n---\n\n";
        continue;
    }
    
    // Get column names
    $fields = mysqli_fetch_fields($dataResult);
    $columns = [];
    foreach ($fields as $field) {
        $columns[] = $field->name;
    }
    
    // Build table header
    $md .= "| " . implode(" | ", $columns) . " |\n";
    $md .= "| " . implode(" | ", array_fill(0, count($columns), "---")) . " |\n";
    
    // Build table rows
    while ($row = mysqli_fetch_assoc($dataResult)) {
        $cells = [];
        foreach ($columns as $col) {
            $value = $row[$col];
            if ($value === null) {
                $cells[] = "_NULL_";
            } else {
                // Truncate very long values (e.g. base64 images)
                $strVal = (string) $value;
                if (strlen($strVal) > 100) {
                    $strVal = substr($strVal, 0, 100) . "…";
                }
                // Escape pipe characters and newlines for markdown table
                $strVal = str_replace("|", "\\|", $strVal);
                $strVal = str_replace("\n", " ", $strVal);
                $strVal = str_replace("\r", "", $strVal);
                $cells[] = $strVal;
            }
        }
        $md .= "| " . implode(" | ", $cells) . " |\n";
    }
    
    mysqli_free_result($dataResult);
    $md .= "\n---\n\n";
}

mysqli_close($db);

// Write to file
$outputPath = __DIR__ . '/database_dump.md';
file_put_contents($outputPath, $md);

echo "Database dump written to: {$outputPath}\n";
echo "Tables exported: " . count($tables) . "\n";
