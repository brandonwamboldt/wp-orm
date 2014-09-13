<?php

namespace WordPress\ORM;

/**
 * Progressively build up a query to get results using an easy to understand
 * DSL.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class Query
{
    /**
     * @var string
     */
    const ORDER_ASCENDING = 'ASC';

    /**
     * @var string
     */
    const ORDER_DESCENDING = 'DESC';

    /**
     * @var integer
     */
    protected $limit = 0;

    /**
     * @var integer
     */
    protected $offset = 0;

    /**
     * @var array
     */
    protected $where = array();

    /**
     * @var string
     */
    protected $sort_by = 'id';

    /**
     * @var string
     */
    protected $order = 'ASC';

    /**
     * @var string|null
     */
    protected $search_term = null;

    /**
     * @var array
     */
    protected $search_fields = array();

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $primary_key;

    /**
     * @param string $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Return the string representation of the query.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->compose_query();
    }

    /**
     * Set the fields to include in the search.
     *
     * @param  array $fields
     */
    public function set_searchable_fields(array $fields)
    {
        $this->search_fields = $fields;
    }

    /**
     * Set the primary key column.
     *
     * @param string $primary_key
     */
    public function set_primary_key($primary_key)
    {
        $this->primary_key = $primary_key;
        $this->sort_by     = $primary_key;
    }

    /**
     * Set the maximum number of results to return at once.
     *
     * @param  integer $limit
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * Set the offset to use when calculating results.
     *
     * @param  integer $offset
     * @return self
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

    /**
     * Set the column we should sort by.
     *
     * @param  string $sort_by
     * @return self
     */
    public function sort_by($sort_by)
    {
        $this->sort_by = $sort_by;

        return $this;
    }

    /**
     * Set the order we should sort by.
     *
     * @param  string $order
     * @return self
     */
    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Add a `=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where($column, $value)
    {
        $this->where[] = array('type' => 'where', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `!=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_not($column, $value)
    {
        $this->where[] = array('type' => 'not', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `LIKE` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_like($column, $value)
    {
        $this->where[] = array('type' => 'like', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `NOT LIKE` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_not_like($column, $value)
    {
        $this->where[] = array('type' => 'not_like', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `<` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_lt($column, $value)
    {
        $this->where[] = array('type' => 'lt', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `<=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_lte($column, $value)
    {
        $this->where[] = array('type' => 'lte', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `>` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_gt($column, $value)
    {
        $this->where[] = array('type' => 'gt', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add a `>=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function where_gte($column, $value)
    {
        $this->where[] = array('type' => 'gte', 'column' => $column, 'value' => $value);

        return $this;
    }

    /**
     * Add an `IN` clause to the search query.
     *
     * @param  string $column
     * @param  array  $value
     * @return self
     */
    public function where_in($column, array $in)
    {
        $this->where[] = array('type' => 'in', 'column' => $column, 'value' => $in);

        return $this;
    }

    /**
     * Add a `NOT IN` clause to the search query.
     *
     * @param  string $column
     * @param  array  $value
     * @return self
     */
    public function where_not_in($column, array $not_in)
    {
        $this->where[] = array('type' => 'not_in', 'column' => $column, 'value' => $not_in);

        return $this;
    }

    /**
     * Add an OR statement to the where clause (e.g. (var = foo OR var = bar OR
     * var = baz)).
     *
     * @param  array $where
     * @return self
     */
    public function where_any(array $where)
    {
        $this->where[] = array('type' => 'any', 'where' => $where);

        return $this;
    }

    /**
     * Add an AND statement to the where clause (e.g. (var1 = foo AND var2 = bar
     * AND var3 = baz)).
     *
     * @param  array $where
     * @return self
     */
    public function where_all(array $where)
    {
        $this->where[] = array('type' => 'all', 'where' => $where);

        return $this;
    }

    /**
     * Get models where any of the designated fields match the given value.
     *
     * @param  string $search_term
     * @return self
     */
    public function search($search_term)
    {
        $this->search_term = $search_term;

        return $this;
    }

    /**
     * Runs the same query as find, but with no limit and don't retrieve the
     * results, just the total items found.
     *
     * @return integer
     */
    public function total_count()
    {
        return $this->find(true);
    }

    /**
     * Compose & execute our query.
     *
     * @param  boolean $only_count Whether to only return the row count
     * @return array
     */
    public function find($only_count = false)
    {
        global $wpdb;

        $model = $this->model;

        // Query
        if ($only_count) {
            return (int) $wpdb->get_var($this->compose_query(true));
        }

        $results = $wpdb->get_results($this->compose_query(false));

        if ($results) {
            foreach ($results as $index => $result) {
                $results[$index] = $model::create((array) $result);
            }
        }

        return $results;
    }

    /**
     * Compose the actual SQL query from all of our filters and options.
     *
     * @param  boolean $only_count Whether to only return the row count
     * @return string
     */
    public function compose_query($only_count = false)
    {
        $model  = $this->model;
        $table  = $model::get_table();
        $where  = '';
        $order  = '';
        $limit  = '';
        $offset = '';

        // Search
        if (!empty($this->search_term)) {
            $where .= ' AND (';

            foreach ($this->search_fields as $field) {
                $where .= '`' . $field . '` LIKE "%' . esc_sql($this->search_term) . '%" OR ';
            }

            $where = substr($where, 0, -4) . ')';
        }

        // Where
        foreach ($this->where as $q) {
            // where
            if ($q['type'] == 'where') {
                $where .= ' AND `' . $q['column'] . '` = "' . esc_sql($q['value']) . '"';
            }

            // where_not
            elseif ($q['type'] == 'not') {
                $where .= ' AND `' . $q['column'] . '` != "' . esc_sql($q['value']) . '"';
            }

            // where_like
            elseif ($q['type'] == 'like') {
                $where .= ' AND `' . $q['column'] . '` LIKE "' . esc_sql($q['value']) . '"';
            }

            // where_not_like
            elseif ($q['type'] == 'not_like') {
                $where .= ' AND `' . $q['column'] . '` NOT LIKE "' . esc_sql($q['value']) . '"';
            }

            // where_lt
            elseif ($q['type'] == 'lt') {
                $where .= ' AND `' . $q['column'] . '` < "' . esc_sql($q['value']) . '"';
            }

            // where_lte
            elseif ($q['type'] == 'lte') {
                $where .= ' AND `' . $q['column'] . '` <= "' . esc_sql($q['value']) . '"';
            }

            // where_gt
            elseif ($q['type'] == 'gt') {
                $where .= ' AND `' . $q['column'] . '` > "' . esc_sql($q['value']) . '"';
            }

            // where_gte
            elseif ($q['type'] == 'gte') {
                $where .= ' AND `' . $q['column'] . '` >= "' . esc_sql($q['value']) . '"';
            }

            // where_in
            elseif ($q['type'] == 'in') {
                $where .= ' AND `' . $q['column'] . '` IN (';

                foreach ($q['value'] as $value) {
                    $where .= '"' . esc_sql($value) . '",';
                }

                $where = substr($where, 0, -1) . ')';
            }

            // where_not_in
            elseif ($q['type'] == 'not_in') {
                $where .= ' AND `' . $q['column'] . '` NOT IN (';

                foreach ($q['value'] as $value) {
                    $where .= '"' . esc_sql($value) . '",';
                }

                $where = substr($where, 0, -1) . ')';
            }

            // where_any
            elseif ($q['type'] == 'any') {
                $where .= ' AND (';

                foreach ($q['where'] as $column => $value) {
                    $where .= '`' . $column . '` = "' . esc_sql($value) . '" OR ';
                }

                $where = substr($where, 0, -5) . ')';
            }

            // where_all
            elseif ($q['type'] == 'all') {
                $where .= ' AND (';

                foreach ($q['where'] as $column => $value) {
                    $where .= '`' . $column . '` = "' . esc_sql($value) . '" AND ';
                }

                $where = substr($where, 0, -5) . ')';
            }
        }

        // Finish where clause
        if (!empty($where)) {
            $where = ' WHERE ' . substr($where, 5);
        }

        // Order
        if (strstr($this->sort_by, '(') !== false && strstr($this->sort_by, ')') !== false) {
            // The sort column contains () so we assume its a function, therefore
            // don't quote it
            $order = ' ORDER BY ' . $this->sort_by . ' ' . $this->order;
        } else {
            $order = ' ORDER BY `' . $this->sort_by . '` ' . $this->order;
        }

        // Limit
        if ($this->limit > 0) {
            $limit = ' LIMIT ' . $this->limit;
        }

        // Offset
        if ($this->offset > 0) {
            $offset = ' OFFSET ' . $this->offset;
        }

        // Query
        if ($only_count) {
            return apply_filters('wporm_count_query', "SELECT COUNT(*) FROM `{$table}`{$where}", $this->model);
        }

        return apply_filters('wporm_query', "SELECT * FROM `{$table}`{$where}{$order}{$limit}{$offset}", $this->model);
    }
}
