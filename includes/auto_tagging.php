<?php
/**
 * Auto Tagging System - Génère des tags à partir du titre et contenu
 */

require_once __DIR__.'/legacy-database.php';

function get_french_stop_words() {
    return [
        'le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'mais', 'donc', 'or', 'ni', 'car',
        'ce', 'cette', 'ces', 'mon', 'ton', 'son', 'notre', 'votre', 'leur', 'mes', 'tes', 'ses', 'nos', 'vos', 'leurs',
        'je', 'tu', 'il', 'elle', 'nous', 'vous', 'ils', 'elles', 'on',
        'me', 'te', 'se', 'lui', 'leur', 'moi', 'toi', 'soi',
        'dans', 'sur', 'sous', 'avec', 'sans', 'pour', 'par', 'contre', 'vers', 'chez', 'entre',
        'qui', 'que', 'quoi', 'dont', 'où', 'si', 'plus', 'moins', 'très', 'trop', 'assez',
        'est', 'sont', 'être', 'avoir', 'fait', 'faire', 'dit', 'dire', 'peut', 'pouvoir',
        'au', 'aux', 'à', 'ne', 'pas', 'non', 'oui', 'bien', 'mal', 'tout', 'tous', 'toute', 'toutes',
        'ça', 'cela', 'ceci', 'comment', 'quand', 'pourquoi', 'aussi', 'encore', 'déjà', 'alors',
        'après', 'avant', 'depuis', 'pendant', 'comme', 'même', 'autre', 'plusieurs', 'quelques',
        'son', 'sa', 'ses', 'leur', 'leurs', 'aucun', 'aucune', 'chaque', 'certain', 'certaine'
    ];
}

function extract_keywords($text, $max_keywords = 10) {
    $text = strtolower($text);
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    $stop_words = get_french_stop_words();
    $filtered_words = array_filter($words, function($word) use ($stop_words) {
        return strlen($word) >= 4 && !in_array($word, $stop_words) && !is_numeric($word);
    });

    $word_freq = array_count_values($filtered_words);
    arsort($word_freq);

    return array_slice(array_keys($word_freq), 0, $max_keywords);
}

function generate_tags_from_article($titre, $contenu, $max_tags = 5) {
    $titre_weight = 3;
    $titre_keywords = extract_keywords($titre, 5);
    $contenu_keywords = extract_keywords(strip_tags($contenu), 15);

    $combined_keywords = [];
    foreach ($titre_keywords as $keyword) {
        $combined_keywords[$keyword] = ($combined_keywords[$keyword] ?? 0) + $titre_weight;
    }
    foreach ($contenu_keywords as $keyword) {
        $combined_keywords[$keyword] = ($combined_keywords[$keyword] ?? 0) + 1;
    }

    arsort($combined_keywords);
    $tags = array_slice(array_keys($combined_keywords), 0, $max_tags);

    return array_map('ucfirst', $tags);
}

function get_or_create_article_tags($article_id, $titre, $contenu) {
    try {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT tags FROM article_tags WHERE article_id = :article_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['tags'])) {
            return json_decode($result['tags'], true);
        }

        $tags = generate_tags_from_article($titre, $contenu);

        $sql = "INSERT INTO article_tags (article_id, tags, created_at, updated_at)
                VALUES (:article_id, :tags, NOW(), NOW())
                ON DUPLICATE KEY UPDATE tags = VALUES(tags), updated_at = NOW()";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $tags_json = json_encode($tags);
        $stmt->bindParam(':tags', $tags_json);
        $stmt->execute();

        return $tags;

    } catch (Exception $e) {
        error_log("Error getting/creating tags: " . $e->getMessage());
        return generate_tags_from_article($titre, $contenu);
    }
}
