<?php

declare(strict_types=1);

/** @var PDO $db Fourni par LegacyInclude */

$page = isset($page) ? max(1, (int) $page) : max(1, (int) ($_GET['page'] ?? 1));
$per_page = 8;
$offset = ($page - 1) * $per_page;

$seed = get_session_seed();
$catsForYou = pick_categories_for_you($seed, 4);

$ph = [];
$paramsCount = [];
foreach ($catsForYou as $i => $cat) {
    $k = ":c{$i}";
    $ph[] = $k;
    $paramsCount[$k] = $cat;
}
$inClause = implode(',', $ph);

$articles = [];
$total_articles = 0;

try {
    if ($ph !== []) {
        $count_stmt = $db->prepare("
            SELECT COUNT(*) AS total
            FROM actualites a
            WHERE a.status = 1
              AND a.statut_validation = 'valide'
              AND a.statut_paiement IN ('paye','gratuit')
              AND a.categorie IN ({$inClause})
        ");
        $count_stmt->execute($paramsCount);
        $total_articles = (int) ($count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $params = $paramsCount + [':seed' => $seed];
        $stmt = $db->prepare("
            SELECT a.*, u.nom AS auteur_nom
            FROM actualites a
            LEFT JOIN users u ON u.id = a.id_redaction
            WHERE a.status = 1
              AND a.statut_validation = 'valide'
              AND a.statut_paiement IN ('paye','gratuit')
              AND a.categorie IN ({$inClause})
            ORDER BY
              COALESCE(a.date_add, a.created_at) DESC,
              (a.id * :seed) % 1000 ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':seed', $seed, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($paramsCount as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Erreur DB juste-pour-vous: '.$e->getMessage());
    $articles = [];
    $total_articles = 0;
}

$total_pages = $total_articles > 0 ? (int) ceil($total_articles / $per_page) : 1;

// --- Bloc sidebar « Ne manquez pas » ---
$avoidDuplicatesMust = false;
$excludeHomeIds = $excludeHomeIds ?? [];
$whereNotInMust = '';
$paramsMust = [];

if ($avoidDuplicatesMust && ! empty($excludeHomeIds)) {
    $phMust = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exmust{$i}";
        $phMust[] = $k;
        $paramsMust[$k] = (int) $id;
    }
    $whereNotInMust = ' AND a.id NOT IN ('.implode(',', $phMust).') ';
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
  {$whereNotInMust}
ORDER BY RAND()
LIMIT 2
";

$stmt = $db->prepare($sqlMust);
$stmt->execute($paramsMust);
$mustRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
