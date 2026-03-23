<?php
require_once 'config/database.php';

/* Load dictionaries for dropdown */
$stmt = $conn->query("
    SELECT dictionary_id, name
    FROM dictionaries
    ORDER BY name ASC
");
$dictionaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Interface - IndicLex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Dictionary Search</h3>
                </div>

                <div class="card-body">
                    <form action="search_results.php" method="GET">

                        <!-- Dictionary Selection -->
                        <div class="mb-3">
                            <label for="dictionary_id" class="form-label">Select Dictionary</label>
                            <select name="dictionary_id" id="dictionary_id" class="form-select">
                                <option value="all">All Dictionaries</option>
                                <?php foreach ($dictionaries as $dictionary): ?>
                                    <option value="<?php echo htmlspecialchars($dictionary['dictionary_id']); ?>">
                                        <?php echo htmlspecialchars($dictionary['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Search Mode -->
                        <div class="mb-3">
                            <label for="mode" class="form-label">Search Mode</label>
                            <select name="mode" id="mode" class="form-select" required>
                                <option value="exact">Exact Match</option>
                                <option value="prefix">Prefix Match</option>
                                <option value="suffix">Suffix Match</option>
                                <option value="substring">Substring Match</option>
                            </select>
                        </div>

                        <!-- Query -->
                        <div class="mb-3">
                            <label for="query" class="form-label">Enter Search Query</label>
                            <input
                                type="text"
                                name="query"
                                id="query"
                                class="form-control"
                                placeholder="Type a word to search"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Search
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>