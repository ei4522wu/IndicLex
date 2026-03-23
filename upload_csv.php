<?php
session_start();

$success = $_SESSION['import_success'] ?? '';
$warning = $_SESSION['import_warning'] ?? '';
$duplicate = $_SESSION['import_duplicate'] ?? '';
$validationErrors = $_SESSION['validation_errors'] ?? [];
$duplicateRows = $_SESSION['duplicate_rows'] ?? [];

unset($_SESSION['import_success']);
unset($_SESSION['import_warning']);
unset($_SESSION['import_duplicate']);
unset($_SESSION['validation_errors']);
unset($_SESSION['duplicate_rows']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IndicLex - Bulk Dictionary Import</title>
    <style>
        body {
            margin: 0;
            font-family: Georgia, serif;
            background: #f5f5f5;
            color: #222;
        }
        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .site-header {
            padding: 18px 14px 0;
        }
        .site-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .container {
            width: 470px;
            margin: 40px auto;
        }
        .panel-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .alert {
            border-radius: 4px;
            padding: 10px 12px;
            margin-bottom: 8px;
            font-size: 14px;
            border: 1px solid transparent;
        }
        .alert-success {
            background: #d8f0de;
            border-color: #a8d5b2;
            color: #216b3a;
        }
        .alert-danger {
            background: #f8d7da;
            border-color: #efb6bc;
            color: #9f2e2e;
        }
        .alert-warning {
            background: #f7efc4;
            border-color: #e3d37f;
            color: #776109;
        }
        details {
            margin-top: 6px;
        }
        details summary {
            cursor: pointer;
            font-weight: 700;
        }
        label {
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
        }
        input[type="file"] {
            margin-bottom: 8px;
        }
        .help-text {
            font-size: 12px;
            color: #555;
            line-height: 1.4;
            margin-bottom: 14px;
        }
        .btn {
            display: inline-block;
            background: #2f6fe4;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            padding: 10px 16px;
            cursor: pointer;
            font-size: 14px;
        }
        .links {
            margin-top: 14px;
            font-size: 13px;
        }
        .links a {
            color: #1a4ed8;
            text-decoration: underline;
            margin-right: 10px;
        }
        .site-footer {
            margin-top: auto;
            padding: 18px 14px;
            font-size: 14px;
        }
        ul.small-list {
            margin: 8px 0 0 18px;
            padding: 0;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="page">
    <header class="site-header">
        <h1>IndicLex</h1>
    </header>

    <main class="container">
        <div class="panel-title">Bulk Dictionary Import (Excel)</div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($warning): ?>
            <div class="alert alert-danger">
                ⚠ <?php echo htmlspecialchars($warning); ?>

                <?php if (!empty($validationErrors)): ?>
                    <details>
                        <summary>Show validation errors</summary>
                        <ul class="small-list">
                            <?php foreach ($validationErrors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($duplicate): ?>
            <div class="alert alert-warning">
                ℹ <?php echo htmlspecialchars($duplicate); ?>

                <?php if (!empty($duplicateRows)): ?>
                    <details>
                        <summary>Show duplicates</summary>
                        <ul class="small-list">
                            <?php foreach ($duplicateRows as $row): ?>
                                <li><?php echo htmlspecialchars($row); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="import_csv.php" method="POST" enctype="multipart/form-data">

    <label for="excel_file">Excel File</label>

    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required>

    <div class="help-text">
        Upload one Excel (.xlsx) dictionary file at a time.<br>
        Required columns: <code>word</code>, <code>part_of_speech</code>, <code>meaning</code>, <code>translation1</code>, <code>translation2</code>
    </div>

    <button type="submit" class="btn">Upload & Import</button>

</form>

        <div class="links">
            <a href="../index.php">← Home</a>
        </div>
    </main>

    <footer class="site-footer">
        © 2026 IndicLex Project
    </footer>
</div>
</body>
</html>