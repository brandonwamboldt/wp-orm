<?php

namespace WordPress\ORM\Admin;

/**
 * Hey
 *
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
class AdminHelper
{
    /**
     * Setup hooks for a new list table page. We'll call $callback, and you
     * should instantiate your list table class and pass it to the
     * render_list_table_page() function along with a page title.
     *
     * @param string   $slug
     * @param callable $callback
     */
    public static function setup_list_table_page($slug, $callback)
    {
        add_action('load-toplevel_page_' . $slug, array(__CLASS__, 'pre_list_table_page'));
        add_action('toplevel_page_' . $slug, $callback);
    }

    /**
     * Tell WordPress not to load the admin header automatically. We want to
     * load it manually when we're ready, so we can still do redirects and what
     * not.
     */
    public static function pre_list_table_page()
    {
        $_GET['noheader'] = true;
    }

    /**
     * Setup the list table and render the list table page, or call the given
     * action.
     *
     * @param string                        $page_title
     * @param WordPress\Orm\Admin\ListTable $list_table
     */
    public static function render_list_table_page($page_title, $list_table)
    {
        // We pass the class to filters & actions
        $list_table_class = get_class($list_table);

        // Get stuff
        $pagenum = $list_table->get_pagenum();
        $action  = $list_table->current_action();

        if ($action) {
            check_admin_referer('bulk-posts');

            $sendback = remove_query_arg(array('trashed', 'untrashed', 'deleted', 'locked', 'ids'), wp_get_referer());

            // Comment this
            if (!$sendback) {
                $sendback = admin_url('admin.php?page=test-venues');
            }

            // Add the current page to the sendback URL
            $sendback = add_query_arg('paged', $pagenum, $sendback);

            // ?
            if (strpos($sendback, 'post.php') !== false) {
                $sendback = admin_url($post_new_file);
            }

            // Get the post ids to operate on
            if ('delete_all' == $doaction) {
                $post_status = preg_replace('/[^a-z0-9_-]+/i', '', $_REQUEST['post_status']);

                if (get_post_status_object($post_status)) {
                    $post_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", $post_type, $post_status));
                }

                $doaction = 'delete';
            } elseif (isset($_REQUEST['media'])) {
                $post_ids = $_REQUEST['media'];
            } elseif (isset($_REQUEST['ids'])) {
                $post_ids = explode(',', $_REQUEST['ids']);
            } elseif (!empty($_REQUEST['post'])) {
                $post_ids = array_map('intval', $_REQUEST['post']);
            }

            if (!isset($post_ids)) {
                wp_redirect($sendback);
                exit;
            }

            // Plugins need to do their thing here
            do_action('wporm:list_table:action', $action, $post_ids, $list_table, $list_table_class);

            // Redirect the user
            $sendback = remove_query_arg([
                'action',
                'action2',
                'tags_input',
                'post_author',
                'comment_status',
                'ping_status',
                '_status',
                'post',
                'bulk_edit',
                'post_view'
            ], $sendback);

            wp_redirect($sendback);
            exit;
        }  elseif (!empty($_REQUEST['_wp_http_referer'])) {
            wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI'])));
            exit;
        }

        $list_table->prepare_items();

        require ABSPATH . 'wp-admin/admin-header.php';
        require __DIR__ . '/../views/list-table.php';
    }
}
