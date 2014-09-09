<?php

namespace WordPress\ORM;

/**
 * Interface for ORM models.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
interface ModelInterface
{
    /**
     * Overwrite this in your concrete class. Returns the table name used to
     * store models of this class.
     *
     * @return string
     */
    public static function get_table();

    /**
     * Get an array of fields to search during a search query.
     *
     * @return array
     */
    public static function get_searchable_fields();
}
