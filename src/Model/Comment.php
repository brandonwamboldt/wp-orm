<?php

namespace WordPress\ORM\Model;

use DateTime;
use WordPress\ORM\BaseModel;

/**
 * WordPress comment model.
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class Comment extends BaseModel
{
    /**
     * @var integer
     */
    protected $comment_ID;

    /**
     * @var integer
     */
    protected $comment_post_ID;

    /**
     * @var string
     */
    protected $comment_author;

    /**
     * @var string
     */
    protected $comment_author_email;

    /**
     * @var string
     */
    protected $comment_author_url;

    /**
     * @var string
     */
    protected $comment_author_IP;

    /**
     * @var DateTime
     */
    protected $comment_date;

    /**
     * @var DateTime
     */
    protected $comment_date_gmt;

    /**
     * @var string
     */
    protected $comment_content;

    /**
     * @var integer
     */
    protected $comment_karma;

    /**
     * @var string
     */
    protected $comment_approved;

    /**
     * @var string
     */
    protected $comment_agent;

    /**
     * @var string
     */
    protected $comment_type;

    /**
     * @var integer
     */
    protected $comment_parent;

    /**
     * @var integer
     */
    protected $user_id;

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

        if (isset($properties['comment_ID'])) {
            $metadata = $wpdb->get_results("SELECT * FROM `{$wpdb->commentmeta}` WHERE `comment_id` = {$properties['comment_ID']}");

            foreach ($metadata as $data) {
                $this->meta[$data->meta_key] = maybe_unserialize($data->meta_value);
            }
        }

        if (isset($properties['comment_date'])) {
            $properties['comment_date'] = new DateTime($properties['comment_date']);
        }

        if (isset($properties['comment_date_gmt'])) {
            $properties['comment_date_gmt'] = new DateTime($properties['comment_date_gmt']);
        }

        parent::__construct($properties);
    }

    /**
     * Get the post's meta data.
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
     * Update the post's meta data.
     *
     * @param string $meta_key
     * @param mixed  $meta_value
     */
    public function update_metadata($meta_key, $meta_value)
    {
        $this->meta[$meta_key] = $meta_value;

        update_comment_meta($this->comment_ID, $meta_key, $meta_value);
    }

    /**
     * Delete the post's meta data.
     *
     * @param string $meta_key
     */
    public function delete_metadata($meta_key)
    {
        unset($this->meta[$meta_key]);

        delete_comment_meta($this->comment_ID, $meta_key);
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
        return 'comment_ID';
    }

    /**
     * Get the table used to store posts.
     *
     * @return string
     */
    public static function get_table()
    {
        global $wpdb;

        return $wpdb->comments;
    }

    /**
     * Get an array of properties to search when doing a search query.
     *
     * @return array
     */
    public static function get_searchable_fields()
    {
        return array('comment_content');
    }
}
