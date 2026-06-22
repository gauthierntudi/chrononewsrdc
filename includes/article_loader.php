<?php
/**
 * Article Loader - Récupère un article par son ID
 */

require_once __DIR__.'/legacy-database.php';

function load_article($article_id) {
    try {
        $db = Database::getInstance()->getConnection();

        $sql = "
        SELECT
            a.*,
            u.nom AS auteur_nom,
            u.cover AS auteur_cover,
            u.titre AS auteur_titre,
            u.Facebook AS auteur_facebook,
            u.Youtube AS auteur_youtube,
            u.Twitter AS auteur_twitter,
            u.Instagram AS auteur_instagram,
            u.bio AS auteur_bio,
            CAST(a.vues AS UNSIGNED) AS vues_int
        FROM actualites a
        LEFT JOIN users u ON u.id = a.id_redaction
        WHERE a.id = :article_id
          AND a.status = 1
          AND a.statut_validation = 'valide'
          AND a.statut_paiement IN ('paye','gratuit')
        LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();

        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return null;
        }

        return $article;

    } catch (Exception $e) {
        error_log("Error loading article: " . $e->getMessage());
        return null;
    }
}

function increment_article_views($article_id, $current_views) {
    try {
        $db = Database::getInstance()->getConnection();

        $new_views = (int)$current_views + 1;

        $sql = "UPDATE actualites SET vues = :views WHERE id = :article_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':views', $new_views, PDO::PARAM_INT);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();

        return $new_views;

    } catch (Exception $e) {
        error_log("Error incrementing views: " . $e->getMessage());
        return $current_views;
    }
}

function get_previous_article($current_article_id, $current_date) {
    try {
        $db = Database::getInstance()->getConnection();

        $sql = "
        SELECT
            id,
            titre,
            cover
        FROM actualites
        WHERE status = 1
          AND statut_validation = 'valide'
          AND statut_paiement IN ('paye','gratuit')
          AND (
              date_add < :current_date
              OR (date_add = :current_date_eq AND id < :current_id)
          )
        ORDER BY date_add DESC, id DESC
        LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':current_date', $current_date);
        $stmt->bindValue(':current_date_eq', $current_date);
        $stmt->bindValue(':current_id', $current_article_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error getting previous article: " . $e->getMessage());
        return null;
    }
}

function get_next_article($current_article_id, $current_date) {
    try {
        $db = Database::getInstance()->getConnection();

        $sql = "
        SELECT
            id,
            titre,
            cover
        FROM actualites
        WHERE status = 1
          AND statut_validation = 'valide'
          AND statut_paiement IN ('paye','gratuit')
          AND (
              date_add > :current_date
              OR (date_add = :current_date_eq AND id > :current_id)
          )
        ORDER BY date_add ASC, id ASC
        LIMIT 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':current_date', $current_date);
        $stmt->bindValue(':current_date_eq', $current_date);
        $stmt->bindValue(':current_id', $current_article_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error getting next article: " . $e->getMessage());
        return null;
    }
}

function get_fallback_articles($article_id, $limit, $db, $exclude_ids = []) {
    $exclude_ids[] = $article_id;
    $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));

    $fetch_limit = $limit * 3;

    $sql = "
    SELECT
        a.id,
        a.titre,
        a.cover,
        a.contenu,
        a.date_add,
        a.categorie AS category_name,
        u.nom AS auteur_nom
    FROM actualites a
    LEFT JOIN users u ON u.id = a.id_redaction
    WHERE a.status = 1
      AND a.statut_validation = 'valide'
      AND a.statut_paiement IN ('paye','gratuit')
      AND a.id NOT IN ($placeholders)
    ORDER BY a.date_add DESC
    LIMIT ?
    ";

    $stmt = $db->prepare($sql);
    $bind_index = 1;
    foreach ($exclude_ids as $id) {
        $stmt->bindValue($bind_index++, $id, PDO::PARAM_INT);
    }
    $stmt->bindValue($bind_index, $fetch_limit, PDO::PARAM_INT);
    $stmt->execute();

    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($articles) <= $limit) {
        return $articles;
    }

    shuffle($articles);
    return array_slice($articles, 0, $limit);
}

function get_similar_articles($article_id, $limit = 4) {
    try {
        $db = Database::getInstance()->getConnection();

        $tags_sql = "SELECT tags FROM article_tags WHERE article_id = :article_id";
        $stmt = $db->prepare($tags_sql);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();
        $tags_result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tags_result || empty($tags_result['tags'])) {
            return get_fallback_articles($article_id, $limit, $db);
        }

        $current_tags = json_decode($tags_result['tags'], true);
        if (empty($current_tags)) {
            return get_fallback_articles($article_id, $limit, $db);
        }

        $sql = "
        SELECT
            a.id,
            a.titre,
            a.cover,
            a.contenu,
            a.date_add,
            a.categorie AS category_name,
            u.nom AS auteur_nom,
            at.tags
        FROM actualites a
        LEFT JOIN users u ON u.id = a.id_redaction
        LEFT JOIN article_tags at ON at.article_id = a.id
        WHERE a.status = 1
          AND a.statut_validation = 'valide'
          AND a.statut_paiement IN ('paye','gratuit')
          AND a.id != :article_id
          AND at.tags IS NOT NULL
        ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();

        $all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scored_articles = [];
        foreach ($all_articles as $article) {
            $article_tags = json_decode($article['tags'], true);
            if (empty($article_tags)) {
                continue;
            }

            $common_tags = array_intersect(
                array_map('mb_strtolower', $current_tags),
                array_map('mb_strtolower', $article_tags)
            );

            $score = count($common_tags);

            if ($score > 0) {
                $article['similarity_score'] = $score;
                $scored_articles[] = $article;
            }
        }

        usort($scored_articles, function($a, $b) {
            if ($b['similarity_score'] === $a['similarity_score']) {
                return strtotime($b['date_add']) - strtotime($a['date_add']);
            }
            return $b['similarity_score'] - $a['similarity_score'];
        });

        $top_similar = array_slice($scored_articles, 0, $limit * 3);

        if (count($top_similar) > $limit) {
            shuffle($top_similar);
            $similar_articles = array_slice($top_similar, 0, $limit);
        } else {
            $similar_articles = $top_similar;
        }

        if (count($similar_articles) < $limit) {
            $exclude_ids = array_map(function($article) {
                return $article['id'];
            }, $similar_articles);

            $remaining_needed = $limit - count($similar_articles);
            $fallback_articles = get_fallback_articles($article_id, $remaining_needed, $db, $exclude_ids);

            $similar_articles = array_merge($similar_articles, $fallback_articles);
        }

        return $similar_articles;

    } catch (Exception $e) {
        error_log("Error getting similar articles: " . $e->getMessage());
        try {
            $db = Database::getInstance()->getConnection();
            return get_fallback_articles($article_id, $limit, $db);
        } catch (Exception $fallback_error) {
            error_log("Error getting fallback articles: " . $fallback_error->getMessage());
            return [];
        }
    }
}