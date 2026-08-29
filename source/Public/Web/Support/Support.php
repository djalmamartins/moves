<?php

namespace Source\Public\Web\Support;

use Source\Core\Connect;
use Source\Core\Controller;
use Source\Models\Support\SupportArticle;
use Source\Models\Support\SupportCategory;

class Support extends Controller
{
    public function __construct()
    {
        parent::__construct(__DIR__ . "/../../../../container/themes/support_by_moves/");
    }

    public function home(): void
    {
        $categories = $this->categories();
        echo $this->view->render("home", ["head" => $this->seo->render("Suporte - " . CONF_SITE_NAME, "Tutoriais e respostas para usar o MovesOS.", url("/suporte"), theme("/assets/images/share.jpg")), "categories" => $categories, "popular" => (new SupportArticle())->findPublished()->order("views DESC, helpful_yes DESC")->limit(6)->fetch(true) ?: []]);
    }

    public function search(): void
    {
        $search = trim(strip_tags((string)($_GET["q"] ?? "")));
        $articles = [];
        if (mb_strlen($search) >= 2) {
            $like = urlencode("%{$search}%");
            $articles = (new SupportArticle())->findPublished("(title LIKE :q1 OR summary LIKE :q2 OR content LIKE :q3)", "q1={$like}&q2={$like}&q3={$like}")->order("views DESC, title")->limit(40)->fetch(true) ?: [];
        }
        echo $this->view->render("search", ["head" => $this->seo->render("Busca no Suporte", "Resultados da busca de suporte.", url("/suporte/buscar?q=" . urlencode($search)), theme("/assets/images/share.jpg")), "search" => $search, "articles" => $articles, "categories" => $this->categories()]);
    }

    public function category(array $data): void
    {
        $section = str_slug($data["section"] ?? $data["category"] ?? "");
        $category = (new SupportCategory())->findByUri($section);
        if (!$category || $category->status !== "active") {
            \Source\Support\AppLogger::log('warning', 'Categoria pública de suporte não localizada', [
                'event_type' => 'support_category_not_found',
                'route_data' => $data,
                'normalized_category' => $section,
            ], 'support');
            redirect("/suporte");
            return;
        }
        echo $this->view->render("category", ["head" => $this->seo->render($category->title . " - Suporte", $category->description, url("/suporte/{$category->uri}"), theme("/assets/images/share.jpg")), "category" => $category, "articles" => $category->articles(true)->order("title")->fetch(true) ?: [], "categories" => $this->categories()]);
    }

    public function article(array $data): void
    {
        $section = str_slug($data["section"] ?? $data["category"] ?? "");
        $category = (new SupportCategory())->findByUri($section);
        $article = (new SupportArticle())->findByUri(str_slug($data["article"] ?? ""), true);
        if (!$category || !$article || (int)$article->category_id !== (int)$category->id) { redirect("/suporte"); return; }
        Connect::getInstance()->prepare("UPDATE support_articles SET views=views+1 WHERE id=:id")->execute(["id" => $article->id]);
        $related = (new SupportArticle())->findPublished("category_id=:category AND id!=:id", "category={$category->id}&id={$article->id}")->order("views DESC,title")->limit(5)->fetch(true) ?: [];
        echo $this->view->render("article", ["head" => $this->seo->render($article->title . " - Suporte", $article->summary, url("/suporte/{$category->uri}/{$article->uri}"), $article->cover ? image($article->cover, 1200, 628) : theme("/assets/images/share.jpg")), "article" => $article, "category" => $category, "related" => $related, "categories" => $this->categories()]);
    }

    public function vote(array $data): void
    {
        header("Content-Type: application/json; charset=utf-8");
        if (!csrf_verify($data)) { echo json_encode(["message" => "Atualize a página e tente novamente."]); return; }
        $articleId = filter_var($data["id"] ?? null, FILTER_VALIDATE_INT);
        $vote = in_array($data["vote"] ?? "", ["yes", "no"], true) ? $data["vote"] : null;
        if (!$articleId || !$vote || !(new SupportArticle())->findById($articleId)) { echo json_encode(["message" => "Avaliação inválida."]); return; }
        $hash = hash("sha256", $articleId . "|" . ($_SERVER["REMOTE_ADDR"] ?? "") . "|" . ($_SERVER["HTTP_USER_AGENT"] ?? ""));
        $pdo = Connect::getInstance();
        $pdo->beginTransaction();
        try {
            $find = $pdo->prepare("SELECT * FROM support_article_votes WHERE article_id=:article AND visitor_hash=:hash FOR UPDATE");
            $find->execute(["article" => $articleId, "hash" => $hash]);
            $old = $find->fetch();
            if (!$old) {
                $pdo->prepare("INSERT INTO support_article_votes (article_id,vote,visitor_hash) VALUES (:article,:vote,:hash)")->execute(["article" => $articleId, "vote" => $vote, "hash" => $hash]);
                $pdo->prepare("UPDATE support_articles SET helpful_{$vote}=helpful_{$vote}+1 WHERE id=:id")->execute(["id" => $articleId]);
            } elseif ($old->vote !== $vote) {
                $pdo->prepare("UPDATE support_article_votes SET vote=:vote WHERE id=:id")->execute(["vote" => $vote, "id" => $old->id]);
                $pdo->prepare("UPDATE support_articles SET helpful_{$old->vote}=GREATEST(0,helpful_{$old->vote}-1),helpful_{$vote}=helpful_{$vote}+1 WHERE id=:id")->execute(["id" => $articleId]);
            }
            $pdo->commit(); echo json_encode(["success" => true, "message" => "Obrigado pela sua avaliação!"]);
        } catch (\Throwable $exception) { if ($pdo->inTransaction()) $pdo->rollBack(); \Source\Support\AppLogger::exception($exception, 'support', ['event_type' => 'support_vote_failed', 'article_id' => $articleId]); echo json_encode(["message" => "Não foi possível registrar sua avaliação."]); }
    }

    private function categories(): array
    {
        return (new SupportCategory())->find("status=:status", "status=active")->order("position,title")->fetch(true) ?: [];
    }
}
