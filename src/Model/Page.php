<?php

namespace WordPress\ORM\Model;

/**
 * WordPress page model.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class Page extends Post
{
    /**
     * @var string
     */
    protected $post_type = 'page';

    /**
     * Start a query to find models matching specific criteria.
     *
     * @return ModelQuery
     */
    public static function query()
    {
        $query = parent::query();
        $query->where('post_type', 'page');

        return $query;
    }
}
