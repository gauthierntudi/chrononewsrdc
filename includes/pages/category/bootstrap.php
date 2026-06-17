<?php

declare(strict_types=1);

/** @var PDO $db Fourni par LegacyInclude */

$category_slug = $category_slug ?? ($_GET['category'] ?? 'Actualités');
$page = isset($page) ? max(1, (int) $page) : max(1, (int) ($_GET['page'] ?? 1));
$per_page = 8;
$offset = ($page - 1) * $per_page;

$available_categories = chrononews_categories();
if (! in_array($category_slug, $available_categories, true)) {
    $category_slug = 'Actualités';
}

try {
    $count_stmt = $db->prepare('
        SELECT COUNT(*) as total
        FROM actualites a
        WHERE a.categorie = :category
        AND a.status = 1
        AND a.statut_validation = \'valide\'
    ');
    $count_stmt->execute(['category' => $category_slug]);
    $total_articles = (int) $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $db->prepare('
        SELECT a.*, u.nom AS auteur_nom
        FROM actualites a
        LEFT JOIN users u ON u.id = a.id_redaction
        WHERE a.categorie = :category
        AND a.status = 1
        AND a.statut_validation = \'valide\'
        ORDER BY a.date_add DESC
        LIMIT :limit OFFSET :offset
    ');
    $stmt->bindValue(':category', $category_slug, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sidebar_stmt = $db->prepare('
        SELECT a.*, u.nom AS auteur_nom
        FROM actualites a
        LEFT JOIN users u ON u.id = a.id_redaction
        WHERE a.status = 1
        AND a.statut_validation = \'valide\'
        ORDER BY RAND()
        LIMIT 2
    ');
    $sidebar_stmt->execute();
    $sidebar_articles = $sidebar_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur DB catégorie: '.$e->getMessage());
    $articles = [];
    $sidebar_articles = [];
    $total_articles = 0;
}

$total_pages = $total_articles > 0 ? (int) ceil($total_articles / $per_page) : 1;

$category_descriptions = chrononews_category_descriptions();

// --- Bloc "À ne pas manquer" (2 articles récents random) ---
$avoidDuplicatesMust = false;
$excludeHomeIds = $excludeHomeIds ?? [];

$whereNotInMust = '';
$paramsMust = [];

if ($avoidDuplicatesMust && ! empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exmust{$i}";
        $ph[] = $k;
        $paramsMust[$k] = (int) $id;
    }
    $whereNotInMust = ' AND a.id NOT IN ('.implode(',', $ph).') ';
}

$sqlMust = "
SELECT a.*, u.nom AS auteur_nom,
       CAST(a.vues AS UNSIGNED) AS vues_int
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND COALESCE(a.date_add, a.created_at) >= (NOW() - INTERVAL 45 DAY)
  $whereNotInMust
ORDER BY RAND()
LIMIT 2
";

$stmt = $db->prepare($sqlMust);
$stmt->execute($paramsMust);
$mustRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
