<?php

namespace WordPress\ORM\Model;

use DateTime;
use WordPress\ORM\BaseModel;

/**
 * WordPress user model.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class User extends BaseModel
{
    /**
     * @var integer
     */
    protected $ID;

    /**
     * @var string
     */
    protected $user_login;

    /**
     * @var string
     */
    protected $user_pass;

    /**
     * @var string
     */
    protected $user_nicename;

    /**
     * @var string
     */
    protected $user_email;

    /**
     * @var string
     */
    protected $user_url;

    /**
     * @var DateTime
     */
    protected $user_registered;

    /**
     * @var string
     */
    protected $user_activation_key;

    /**
     * @var string
     */
    protected $user_status;

    /**
     * @var string
     */
    protected $display_name;

    /**
     * @var array
     */
    protected $meta = array();

    /**
     * Override the default constructor so we can type cast certain properties.
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        global $wpdb;

        if (isset($properties['ID'])) {
            $metadata = $wpdb->get_results("SELECT * FROM `{$wpdb->usermeta}` WHERE `user_id` = {$properties['ID']}");

            foreach ($metadata as $data) {
                $this->meta[$data->meta_key] = maybe_unserialize($data->meta_value);
            }
        }

        if (isset($properties['user_registered'])) {
            $properties['user_registered'] = new DateTime($properties['user_registered']);
        }

        parent::__construct($properties);
    }

    /**
     * Get the user's meta data.
     *
     * @param  string $meta_key
     * @param  mixed  $default
     * @return mixed
     */
    public function get_metadata($meta_key, $default = null)
    {
        return $this->meta[$meta_key];
    }

    /**
     * Update the user's meta data.
     *
     * @param string $meta_key
     * @param mixed  $meta_value
     */
    public function update_metadata($meta_key, $meta_value)
    {
        $this->meta[$meta_key] = $meta_value;

        update_user_meta($this->ID, $meta_key, $meta_value);
    }

    /**
     * Delete the user's meta data.
     *
     * @param string $meta_key
     */
    public function delete_metadata($meta_key)
    {
        unset($this->meta[$meta_key]);

        delete_user_meta($this->ID, $meta_key);
    }

    /**
     * Convert complex objects to strings to insert into the database.
     *
     * @param  array $props
     * @return array
     */
    public function flatten_props($props)
    {
        unset($props['meta']);

        return parent::flatten_props($props);
    }

    /**
     * Get the model's primary key.
     *
     * @return string
     */
    public static function get_primary_key()
    {
        return 'ID';
    }

    /**
     * Get the table used to store posts.
     *
     * @return string
     */
    public static function get_table()
    {
        global $wpdb;

        return $wpdb->users;
    }

    /**
     * Get an array of properties to search when doing a search query.
     *
     * @return array
     */
    public static function get_searchable_fields()
    {
        return array('user_login', 'user_nicename', 'user_email', 'display_name');
    }
}
