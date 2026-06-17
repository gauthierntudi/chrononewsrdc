<?php

declare(strict_types=1);

/** @var PDO $db Fourni par LegacyInclude */
$excludeHomeIds = $excludeHomeIds ?? [];

if (! legacy_front_schema_ready($db)) {
    $homeHero = null;
    $homeHeroId = null;
    $homeSmalls = [];
    $topSmallId = null;
    $maxVues = 0;
    $forYouBig = null;
    $forYouSmalls = [];
    $ActualitésPosts = [];
    $maxVuesActualités = 1000;
    $lic1Big = null;
    $lic1Smalls = [];
    $lic2Big = null;
    $lic2Smalls = [];
    $mustRows = [];
    $ecoBig = null;
    $ecoSmalls = [];
    $invBig = null;
    $invSmalls = [];
    $energyPosts = [];
    $cultureBig = null;
    $cultureSmalls = [];
    $mvBig = null;
    $mvSmalls = [];
    $adTop = null;

    return;
}

/** 1) Grand article à la une **/
$sqlHero = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND a.alaune = 'YES'
  AND COALESCE(a.date_add, a.created_at) >= (NOW() - INTERVAL 45 DAY)
ORDER BY RAND()
LIMIT 1
";
$homeHero = $db->query($sqlHero)->fetch(PDO::FETCH_ASSOC);
$homeHeroId = $homeHero['id'] ?? null;


/** 2) 4 petits articles récents (différents du grand) **/
$sqlSmall = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND (:heroId IS NULL OR a.id <> :heroIdExclude)
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 4
";
$stmt = $db->prepare($sqlSmall);
$stmt->execute([':heroId' => $homeHeroId, ':heroIdExclude' => $homeHeroId]);
$homeSmalls = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** 3) Déterminer le plus vu parmi les 4 (pour donut) **/
$topSmallId = null;
$maxVues = 0;

foreach ($homeSmalls as $it) {
    $vv = vues_int($it['vues'] ?? 0);
    if ($vv > $maxVues) {
        $maxVues = $vv;
        $topSmallId = (int)$it['id'];
    }
}



//requête artiles Juste pour vous
$avoidDuplicates = true; // ✅ quand tu voudras activer, mets true

$seed = get_session_seed();
$catsForYou = pick_categories_for_you($seed, 4); // 4 catégories

// placeholders nommés
$ph = [];
$params = [':seed' => $seed];
foreach ($catsForYou as $i => $cat) {
    $k = ":c{$i}";
    $ph[] = $k;
    $params[$k] = $cat;
}

$sqlForYou = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND a.categorie IN (" . implode(',', $ph) . ")
ORDER BY
  -- d'abord un peu de récence
  COALESCE(a.date_add, a.created_at) DESC,
  -- puis un mélange stable par visiteur
  (a.id * :seed) % 1000 ASC
LIMIT 5
";

$stmt = $db->prepare($sqlForYou);
$stmt->execute($params);
$forYouRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$forYouBig = $forYouRows[0] ?? null;
$forYouSmalls = array_slice($forYouRows, 1, 4);

// Optionnel : ajouter aussi ces ids à la liste globale (utile pour bloc 3, 4...)
add_exclude_ids($excludeHomeIds, $forYouRows);

//requête artiles Juste pour vous




// --- Bloc 3 : Actualités ---
$catActualités = 'Actualités';

$sqlActualités = "
SELECT a.*,
       u.nom AS auteur_nom,
       CAST(a.vues AS UNSIGNED) AS vues_int
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) = :cat
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 6
";
$stmt = $db->prepare($sqlActualités);
$stmt->execute([':cat' => $catActualités]);
$ActualitésPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Max vues du bloc basé sur vues_int (pas vues)
$maxVuesActualités = 1000;

foreach ($ActualitésPosts as $row) {
    $v = (int)($row['vues_int'] ?? 0);
    if ($v > $maxVuesActualités) $maxVuesActualités = $v;
}

// --- Bloc 3 : Actualités ---


// --- Bloc Interview (anti-répétition interne) ---
$catLic = 'Interviews';

$avoidGlobalExclude = false; // true => évite doublons avec d'autres blocs via $excludeHomeIds
$whereNotIn = '';
$params = [':cat' => $catLic];

if ($avoidGlobalExclude && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exlic{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlLic = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) = :cat
  $whereNotIn
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 12
";

$stmt = $db->prepare($sqlLic);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Dédup STRICT uniquement dans Interview (pas de répétition entre sous-blocs)
$seen = [];
$licRows = [];
foreach ($rows as $r) {
    $id = (int)($r['id'] ?? 0);
    if ($id <= 0) continue;
    if (isset($seen[$id])) continue;
    $seen[$id] = true;
    $licRows[] = $r;
    if (count($licRows) >= 8) break; // 8 uniques max pour tes 2 sous-blocs
}

// ✅ Split (0 répétition)
$lic1Big    = $licRows[0] ?? null;
$lic1Smalls = array_slice($licRows, 1, 3);
$lic2Big    = $licRows[4] ?? null;
$lic2Smalls = array_slice($licRows, 5, 3);

// Optionnel : si tu veux enregistrer pour les autres blocs (global)
$useAddExclude = false; // true/false
if ($useAddExclude) {
    add_exclude_ids($excludeHomeIds, $licRows);
}
// --- /Bloc Interview ---


// --- Bloc "À ne pas manquer" (2 articles récents random) ---
$avoidDuplicatesMust = false; // ✅ spécifique à ce bloc, ne touche pas $avoidDuplicates (Juste pour vous)

$whereNotInMust = '';
$paramsMust = [];

if ($avoidDuplicatesMust && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exmust{$i}";
        $ph[] = $k;
        $paramsMust[$k] = (int)$id;
    }
    $whereNotInMust = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
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

// Optionnel (si tu veux éviter doublons dans les blocs suivants)
// if ($avoidDuplicatesMust) add_exclude_ids($excludeHomeIds, $mustRows);
// --- /Bloc "À ne pas manquer" ---


// --- Bloc Economie ---
$catEconomie = 'Économie';

// ⚠️ pour éviter conflit avec $avoidDuplicates du bloc "Juste pour vous"
$avoidDuplicatesEconomie = false; // mets true quand tu voudras éviter doublons avec autres blocs

$whereNotIn = '';
$params = [':cat' => $catEconomie];

if ($avoidDuplicatesEconomie && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":execo{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlEco = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) = :cat
  $whereNotIn
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 5
";

$stmt = $db->prepare($sqlEco);
$stmt->execute($params);
$ecoRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback si pas assez d'articles : on répète (sans “vide” dans le bloc)
if (!function_exists('fill_repeat')) {
  function fill_repeat(array $rows, int $needed): array {
      if (count($rows) >= $needed) return array_slice($rows, 0, $needed);
      if (empty($rows)) return [];
      $out = $rows;
      $i = 0;
      while (count($out) < $needed) {
          $out[] = $rows[$i % count($rows)];
          $i++;
      }
      return $out;
  }
}

$ecoRows = fill_repeat($ecoRows, 5);

$ecoBig    = $ecoRows[0] ?? null;
$ecoSmalls = array_slice($ecoRows, 1, 4);

// Optionnel : si tu veux plus tard éviter doublons blocs suivants
if (!empty($avoidDuplicatesEconomie) && function_exists('add_exclude_ids')) {
    add_exclude_ids($excludeHomeIds, $ecoRows);
}
// --- /Bloc Economie ---


// --- Bloc Politique ---
$catInnov = 'Politique'; // catégorie en base
$labelInnov = 'Politique'; // label à afficher si tu veux garder le singulier

// ⚠️ évite conflit avec $avoidDuplicates existant ailleurs
$avoidDuplicatesInnov = false;

$whereNotIn = '';
$params = [':cat' => $catInnov];

if ($avoidDuplicatesInnov && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exinv{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlInv = "
SELECT a.*, u.nom AS auteur_nom
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) = :cat
  $whereNotIn
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 4
";

$stmt = $db->prepare($sqlInv);
$stmt->execute($params);
$invRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback si pas assez : répète pour éviter vide
if (!function_exists('fill_repeat')) {
  function fill_repeat(array $rows, int $needed): array {
      if (count($rows) >= $needed) return array_slice($rows, 0, $needed);
      if (empty($rows)) return [];
      $out = $rows;
      $i = 0;
      while (count($out) < $needed) {
          $out[] = $rows[$i % count($rows)];
          $i++;
      }
      return $out;
  }
}
$invRows = fill_repeat($invRows, 4);

$invBig    = $invRows[0] ?? null;
$invSmalls = array_slice($invRows, 1, 3);

// optionnel : si tu veux éviter doublons dans les blocs suivants
if ($avoidDuplicatesInnov && function_exists('add_exclude_ids')) {
    add_exclude_ids($excludeHomeIds, $invRows);
}
// --- /Bloc Politique ---


// --- Bloc International ---
$catEnergy = 'International';
$labelEnergy = 'International';

// ⚠️ éviter conflit avec $avoidDuplicates global
$avoidDuplicatesEnergy = false;

$whereNotIn = '';
$params = [':cat' => $catEnergy, ':catLegacy' => "Int\u2019l"];

if ($avoidDuplicatesEnergy && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exen{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlEnergy = "
SELECT a.*,
       u.nom AS auteur_nom,
       CAST(a.vues AS UNSIGNED) AS vues_int
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) IN (:cat, :catLegacy)
  $whereNotIn
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 4
";
$stmt = $db->prepare($sqlEnergy);
$stmt->execute($params);
$energyPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback anti-vide (répète si pas assez)
$energyPosts = fill_repeat($energyPosts, 4);

// optionnel : si tu veux éviter doublons dans les blocs suivants
if ($avoidDuplicatesEnergy && function_exists('add_exclude_ids')) {
    add_exclude_ids($excludeHomeIds, $energyPosts);
}
// --- /Bloc Int’l ---


// --- Bloc Events ---
$catCulture = 'Société';
$uCatCulture = category_url($catCulture);

// ⚠️ éviter conflit avec $avoidDuplicates global
$avoidDuplicatesEvents = false;

$whereNotIn = '';
$params = [':cat' => $catCulture];

if ($avoidDuplicatesEvents && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":excu{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlEvents = "
SELECT a.*,
       u.nom AS auteur_nom,
       CAST(a.vues AS UNSIGNED) AS vues_int
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  AND TRIM(a.categorie) = :cat
  $whereNotIn
ORDER BY COALESCE(a.date_add, a.created_at) DESC
LIMIT 5
";
$stmt = $db->prepare($sqlEvents);
$stmt->execute($params);
$cultureRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback anti-vide : répète pour toujours avoir 5 éléments
$cultureRows = fill_repeat($cultureRows, 5);

$cultureBig = $cultureRows[0] ?? null;
$cultureSmalls = array_slice($cultureRows, 1, 4);

// optionnel : exclusion globale
if ($avoidDuplicatesEvents && function_exists('add_exclude_ids')) {
    add_exclude_ids($excludeHomeIds, $cultureRows);
}
// --- /Bloc Events ---


// --- Bloc : Les plus vus ---
$avoidDuplicatesMostViewed = false; // ⚠️ évite conflit avec $avoidDuplicates global

$whereNotIn = '';
$params = [];

if ($avoidDuplicatesMostViewed && !empty($excludeHomeIds)) {
    $ph = [];
    foreach ($excludeHomeIds as $i => $id) {
        $k = ":exmv{$i}";
        $ph[] = $k;
        $params[$k] = (int)$id;
    }
    $whereNotIn = " AND a.id NOT IN (" . implode(',', $ph) . ") ";
}

$sqlMostViewed = "
SELECT a.*,
       u.nom AS auteur_nom,
       CAST(a.vues AS UNSIGNED) AS vues_int
FROM actualites a
LEFT JOIN users u ON u.id = a.id_redaction
WHERE a.status = 1
  AND a.statut_validation = 'valide'
  AND a.statut_paiement IN ('paye','gratuit')
  $whereNotIn
ORDER BY CAST(a.vues AS UNSIGNED) DESC,
         COALESCE(a.date_add, a.created_at) DESC
LIMIT 3
";

$stmt = $db->prepare($sqlMostViewed);
$stmt->execute($params);
$mostViewedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fallback (si pas assez d’articles)
$mostViewedRows = fill_repeat($mostViewedRows, 3);

$mvBig = $mostViewedRows[0] ?? null;
$mvSmalls = array_slice($mostViewedRows, 1, 2);

// optionnel : exclusion globale
if ($avoidDuplicatesMostViewed && function_exists('add_exclude_ids')) {
    add_exclude_ids($excludeHomeIds, $mostViewedRows);
}
// --- /Bloc : Les plus vus ---


$sqlAd = "
SELECT id, titre, image_url, url_cible
FROM publicites
WHERE format = 'paysage_small'
  AND statut_validation = 'valide'
  AND statut_diffusion = 'active'
  AND CURDATE() BETWEEN date_debut AND date_fin
ORDER BY RAND()
LIMIT 1
";
$adTop = $db->query($sqlAd)->fetch(PDO::FETCH_ASSOC);
?>
