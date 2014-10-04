<?php

namespace WordPress\ORM;

/**
 * Base class for building models.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
abstract class BaseModel implements ModelInterface
{
    /**
     * Get the column used as the primary key, defaults to 'id'.
     *
     * @return string
     */
    public static function get_primary_key()
    {
        return 'id';
    }

    /**
     * Constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        $model_props = $this->properties();
        $properties  = array_intersect_key($properties, $model_props);

        foreach ($properties as $property => $value) {
            $this->{$property} = maybe_unserialize($value);
        }
    }

    /**
     * Magically handle getters and setters.
     *
     * @param  string $function
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($function, $arguments)
    {
        // Getters following the pattern 'get_{$property}'
        if (substr($function, 0, 4) == 'get_') {
            $model_props = $this->properties();
            $property    = substr($function, 4);

            if (array_key_exists($property, $model_props)) {
                return $this->{$property};
            }
        }

        // Setters following the pattern 'set_{$property}'
        if (substr($function, 0, 4) == 'set_') {
            $model_props = $this->properties();
            $property    = substr($function, 4);

            if (array_key_exists($property, $model_props)) {
                $this->{$property} = $arguments[0];
            }
        }
    }

    /**
     * Return the value of the primary key.
     *
     * @return integer
     */
    public function primary_key()
    {
        return $this->{static::get_primary_key()};
    }

    /**
     * Get all of the properties of this model as an array.
     *
     * @return array
     */
    public function to_array()
    {
        return $this->properties();
    }

    /**
     * Convert complex objects to strings to insert into the database.
     *
     * @param  array $props
     * @return array
     */
    public function flatten_props($props)
    {
        foreach ($props as $property => $value) {
            if (is_object($value) && get_class($value) == 'DateTime') {
                $props[$property] = $value->format('Y-m-d H:i:s');
            } elseif (is_array($value)) {
                $props[$property] = serialize($value);
            } elseif ($value instanceof AbstractClass) {
                $props[$property] = $value->primary_key();
            }
        }

        return $props;
    }

    /**
     * Return an array of all the properties for this model. By default, returns
     * every class variable.
     *
     * @return array
     */
    public function properties()
    {
        return get_object_vars($this);
    }

    /**
     * Save this model to the database. Will create a new record if the ID
     * property isn't set, or update an existing record if the ID property is
     * set.
     *
     * @return integer
     */
    public function save()
    {
        global $wpdb;

        // Get the model's properties
        $props = $this->properties();

        // Flatten complex objects
        $props = $this->flatten_props($props);

        // Insert or update?
        if (is_null($props[static::get_primary_key()])) {
            $wpdb->insert($this->get_table(), $props);

            $this->{static::get_primary_key()} = $wpdb->insert_id;
        } else {
            $wpdb->update(static::get_table(), $props, array(static::get_primary_key() => $this->{static::get_primary_key()}));
        }

        return $this->id;
    }

    /**
     * Create a new model from the given data.
     *
     * @return self
     */
    public static function create($properties)
    {
        return new static($properties);
    }

    /**
     * Delete the model from the database. Returns true if it was successful
     * or false if it was not.
     *
     * @return boolean
     */
    public function delete()
    {
        global $wpdb;

        return $wpdb->delete(static::get_table(), array(static::get_primary_key() => $this->{static::get_primary_key()}));
    }

    /**
     * Find a specific model by a given property value.
     *
     * @param  string $property
     * @param  string $value
     * @return false|self
     */
    public static function find_one_by($property, $value)
    {
        global $wpdb;

        // Escape the value
        $value = esc_sql($value);

        // Get the table name
        $table = static::get_table();

        // Get the item
        $obj = $wpdb->get_row("SELECT * FROM `{$table}` WHERE `{$property}` = '{$value}'", ARRAY_A);

        // Return false if no item was found, or a new model
        return ($obj ? static::create($obj) : false);
    }

    /**
     * Find a specific model by it's unique ID.
     *
     * @param  integer $id
     * @return false|self
     */
    public static function find_one($id)
    {
        return static::find_one_by(static::get_primary_key(), (int) $id);
    }

    /**
     * Start a query to find models matching specific criteria.
     *
     * @return Query
     */
    public static function query()
    {
        $query = new Query(get_called_class());
        $query->set_searchable_fields(static::get_searchable_fields());
        $query->set_primary_key(static::get_primary_key());

        return $query;
    }

    /**
     * Return EVERY instance of this model from the database, with NO filtering.
     *
     * @return array
     */
    public static function all()
    {
        global $wpdb;

        // Get the table name
        $table = static::get_table();

        // Get the items
        $results = $wpdb->get_results("SELECT * FROM `{$table}`");

        foreach ($results as $index => $result) {
            $results[$index] = static::create((array) $result);
        }

        return $results;
    }

    /**
     * Return configured table prefix.
     * @return string
     */
    public function get_table_prefix()
    {
        global $wpdb;
        return $wpdb->prefix;
    }
}
