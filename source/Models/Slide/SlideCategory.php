<?php

namespace Source\Models\Slide;

use Source\Core\Model;

/**
 * @deprecated Use CategorySlide. Mantido temporariamente como alias compatível.
 * @package Source\Models
 */
class SlideCategory extends Model
{
    /**
     * SlideCategory constructor.
     */
    public function __construct()
    {
        parent::__construct("categories_slide", ["id"], ["title", "description"]);
    }

    /**
     * @param string $uri
     * @param string $columns
     * @return null|SlideCategory
     */
    public function findByUri(string $uri, string $columns = "*"): ?SlideCategory
    {
        $find = $this->find("uri = :uri", "uri={$uri}", $columns);
        return $find->fetch();
    }

    /**
     * @return Post
     */
    public function posts(): AppSlide
    {
        return (new AppSlide())->find("category = :id", "id={$this->id}");
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $checkUri = (new SlideCategory())->find("uri = :uri AND id != :id", "uri={$this->uri}&id={$this->id}");

        if ($checkUri->count()) {
            $this->uri = "{$this->uri}-{$this->lastId()}";
        }

        return parent::save();
    }
}
