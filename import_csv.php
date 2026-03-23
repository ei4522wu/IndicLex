
<?php
session_start();
require_once 'config/database.php';

function redirectBack() {
    header('Location: upload_csv.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['import_warning'] = 'Invalid request.';
    redirectBack();
}

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['import_warning'] = 'File upload failed.';
    redirectBack();
}

$fileName = $_FILES['excel_file']['name'];
$tmpPath = $_FILES['excel_file']['tmp_name'];
$fileSize = $_FILES['excel_file']['size'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExt, ['xlsx', 'xls'])) {
    $_SESSION['import_warning'] = 'Only Excel files (.xlsx, .xls) are allowed.';
    redirectBack();
}

if ($fileSize > 10 * 1024 * 1024) {
    $_SESSION['import_warning'] = 'The file is larger than 10 MB.';
    redirectBack();
}
$dictionaryName = preg_replace('/\.csv$/i', '', $fileName);
$dictionaryName = str_replace(['_', '-'], ' ', $dictionaryName);
$dictionaryName = ucwords(trim($dictionaryName));

$handle = fopen($tmpPath, 'r');
if (!$handle) {
    $_SESSION['import_warning'] = 'Could not open the CSV file.';
    redirectBack();
}

$firstLine = fgets($handle);
rewind($handle);

$delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

$header = fgetcsv($handle, 0, $delimiter);

if (!$header) {
    fclose($handle);
    $_SESSION['import_warning'] = 'The CSV file is empty.';
    redirectBack();
}

$header = array_map(function ($value) {
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    return strtolower(trim($value));
}, $header);

$requiredColumns = ['word', 'part_of_speech', 'meaning', 'translation1', 'translation2'];

foreach ($requiredColumns as $col) {
    if (!in_array($col, $header, true)) {
        fclose($handle);
        $_SESSION['import_warning'] = "Missing required column: {$col}";
        redirectBack();
    }
}

$headerMap = array_flip($header);

$validationErrors = [];
$duplicateRows = [];
$insertedCount = 0;
$skippedCount = 0;
$duplicateCount = 0;

try {
    $conn->beginTransaction();

    $checkDictionary = $conn->prepare("SELECT id FROM dictionaries WHERE name = ? LIMIT 1");
    $checkDictionary->execute([$dictionaryName]);
    $dictionary = $checkDictionary->fetch(PDO::FETCH_ASSOC);

    if ($dictionary) {
        $dictionaryId = (int)$dictionary['id'];
    } else {
        $insertDictionary = $conn->prepare("
            INSERT INTO dictionaries (name, source_language, target_language)
            VALUES (?, ?, ?)
        ");
        $insertDictionary->execute([$dictionaryName, 'Unknown', 'Unknown']);
        $dictionaryId = (int)$conn->lastInsertId();
    }

    $checkEntry = $conn->prepare("
        SELECT id
        FROM dictionary_entries
        WHERE dictionary_id = ? AND source_word = ? AND target_word = ?
        LIMIT 1
    ");

    $insertEntry = $conn->prepare("
        INSERT INTO dictionary_entries
        (dictionary_id, source_word, target_word, part_of_speech, example_sentence)
        VALUES (?, ?, ?, ?, ?)
    ");

    $rowNumber = 1;

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rowNumber++;

        $word = trim($row[$headerMap['word']] ?? '');
        $partOfSpeech = trim($row[$headerMap['part_of_speech']] ?? '');
        $meaning = trim($row[$headerMap['meaning']] ?? '');
        $translation1 = trim($row[$headerMap['translation1']] ?? '');
        $translation2 = trim($row[$headerMap['translation2']] ?? '');

        if ($word === '' && $partOfSpeech === '' && $meaning === '' && $translation1 === '' && $translation2 === '') {
            continue;
        }

        if ($word === '' || $meaning === '') {
            $skippedCount++;
            $validationErrors[] = "Row {$rowNumber}: missing required value in word or meaning.";
            continue;
        }

        $targetWord = $translation1 !== '' ? $translation1 : $meaning;
        $exampleSentence = $translation2 !== '' ? $translation2 : null;

        $checkEntry->execute([$dictionaryId, $word, $targetWord]);
        if ($checkEntry->fetch(PDO::FETCH_ASSOC)) {
            $duplicateCount++;
            $skippedCount++;
            $duplicateRows[] = "Row {$rowNumber}: {$word} → {$targetWord}";
            continue;
        }

        $insertEntry->execute([
            $dictionaryId,
            $word,
            $targetWord,
            ($partOfSpeech !== '' ? $partOfSpeech : null),
            $exampleSentence
        ]);

        $insertedCount++;
    }

    fclose($handle);
    $conn->commit();

    $_SESSION['import_success'] = "Import complete — added to {$dictionaryName}: {$insertedCount} rows inserted, {$skippedCount} skipped.";

    if (!empty($validationErrors)) {
        $_SESSION['import_warning'] = count($validationErrors) . " row(s) had validation errors and were skipped.";
        $_SESSION['validation_errors'] = $validationErrors;
    }

    if ($duplicateCount > 0) {
        $_SESSION['import_duplicate'] = "{$duplicateCount} duplicate(s) detected and skipped.";
        $_SESSION['duplicate_rows'] = $duplicateRows;
    }

    redirectBack();

} catch (Exception $e) {
    fclose($handle);

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    $_SESSION['import_warning'] = 'Import failed: ' . $e->getMessage();
    redirectBack();
}