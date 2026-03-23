<?php
require_once 'config/database.php';

/* Get and validate input */
$dictionary_id = $_GET['dictionary_id'] ?? 'all';
$mode = $_GET['mode'] ?? 'exact';
$query = trim($_GET['query'] ?? '');
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$allowed_modes = ['exact', 'prefix', 'suffix', 'substring'];
if (!in_array($mode, $allowed_modes, true)) {
    $mode = 'exact';
}

if ($page < 1) {
    $page = 1;
}

$results_per_page = 10;
$offset = ($page - 1) * $results_per_page;

$results = [];
$total_results = 0;
$total_pages = 0;
$error = '';

if ($query === '') {
    $error = 'Please enter a search query.';
} else {
    /* Build search pattern based on mode */
    switch ($mode) {
        case 'exact':
            $search_value = $query;
            $comparison_sql = "e.word = :query";
            break;

        case 'prefix':
            $search_value = $query . '%';
            $comparison_sql = "e.word LIKE :query";
            break;

        case 'suffix':
            $search_value = '%' . $query;
            $comparison_sql = "e.word LIKE :query";
            break;

        case 'substring':
            $search_value = '%' . $query . '%';
            $comparison_sql = "e.word LIKE :query";
            break;

        default:
            $search_value = $query;
            $comparison_sql = "e.word = :query";
            break;
    }

    /* Base WHERE clause */
    $where_sql = "WHERE $comparison_sql";
    $params = [':query' => $search_value];

    if ($dictionary_id !== 'all' && ctype_digit((string)$dictionary_id)) {
        $where_sql .= " AND e.dictionary_id = :dictionary_id";
        $params[':dictionary_id'] = (int)$dictionary_id;
    }

    /* Count total results */
    $count_sql = "
        SELECT COUNT(*) 
        FROM dictionary_entries e
        INNER JOIN dictionaries d ON e.dictionary_id = d.dictionary_id
        $where_sql
    ";

    $count_stmt = $conn->prepare($count_sql);
    foreach ($params as $key => $value) {
        if ($key === ':dictionary_id') {
            $count_stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $count_stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    $count_stmt->execute();
    $total_results = (int) $count_stmt->fetchColumn();

    $total_pages = (int) ceil($total_results / $results_per_page);

    /* Get paginated results */
    $search_sql = "
        SELECT 
            e.entry_id,
            e.word,
            e.part_of_speech,
            e.meaning,
            e.translation1,
            e.translation2,
            d.name AS dictionary_name
        FROM dictionary_entries e
        INNER JOIN dictionaries d ON e.dictionary_id = d.dictionary_id
        $where_sql
        ORDER BY e.word ASC
        LIMIT :limit OFFSET :offset
    ";

    $search_stmt = $conn->prepare($search_sql);

    foreach ($params as $key => $value) {
        if ($key === ':dictionary_id') {
            $search_stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $search_stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }

    $search_stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
    $search_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $search_stmt->execute();
    $results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* Helper for pagination links */
function buildPageUrl($pageNumber, $dictionary_id, $mode, $query) {
    return 'search_results.php?' . http_build_query([
        'dictionary_id' => $dictionary_id,
        'mode' => $mode,
        'query' => $query,
        'page' => $pageNumber
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - IndicLex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Search Results</h2>
        <a href="search.php" class="btn btn-secondary">Back to Search</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p class="mb-1"><strong>Query:</strong> <?php echo htmlspecialchars($query); ?></p>
            <p class="mb-1"><strong>Mode:</strong> <?php echo htmlspecialchars(ucfirst($mode)); ?></p>
            <p class="mb-0"><strong>Total Results:</strong> <?php echo $total_results; ?></p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif ($total_results === 0): ?>
        <div class="alert alert-warning">
            No matching entries were found.
        </div>
    <?php else: ?>

        <div class="row">
            <?php foreach ($results as $row): ?>
                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h4 class="mb-2"><?php echo htmlspecialchars($row['word']); ?></h4>

                            <p class="mb-1">
                                <strong>Dictionary:</strong>
                                <?php echo htmlspecialchars($row['dictionary_name']); ?>
                            </p>

                            <?php if (!empty($row['part_of_speech'])): ?>
                                <p class="mb-1">
                                    <strong>Part of Speech:</strong>
                                    <?php echo htmlspecialchars($row['part_of_speech']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($row['meaning'])): ?>
                                <p class="mb-1">
                                    <strong>Meaning:</strong>
                                    <?php echo htmlspecialchars($row['meaning']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($row['translation1'])): ?>
                                <p class="mb-1">
                                    <strong>Translation 1:</strong>
                                    <?php echo htmlspecialchars($row['translation1']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($row['translation2'])): ?>
                                <p class="mb-0">
                                    <strong>Translation 2:</strong>
                                    <?php echo htmlspecialchars($row['translation2']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Search pagination">
                <ul class="pagination justify-content-center mt-4">

                    <!-- Previous -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ($page > 1) ? htmlspecialchars(buildPageUrl($page - 1, $dictionary_id, $mode, $query)) : '#'; ?>">
                            Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars(buildPageUrl($i, $dictionary_id, $mode, $query)); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next -->
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ($page < $total_pages) ? htmlspecialchars(buildPageUrl($page + 1, $dictionary_id, $mode, $query)) : '#'; ?>">
                            Next
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>

</body>
</html>