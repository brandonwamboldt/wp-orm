<div class="wrap">
	<?php screen_icon() ?>
	<h2>
		<?php
		echo apply_filters('wporm:list_page_title', $page_title);

		// Action buttons
		do_action('wporm:list_page_actions');

		// Searching
		if (!empty($_REQUEST['s'])) {
			printf(' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', $list_table->get_search_query());
		}
		?>
	</h2>

	<?php
	if (isset($_REQUEST['locked']) || isset($_REQUEST['updated']) || isset($_REQUEST['deleted']) || isset($_REQUEST['trashed']) || isset($_REQUEST['untrashed'])) {
		$messages = array();

		echo '<div id="message" class="updated"><p>';

		if (isset($_REQUEST['updated']) && $updated = absint($_REQUEST['updated'])) {
			$messages[] = sprintf(_n('%s post updated.', '%s posts updated.', $updated), number_format_i18n($updated));
		}

		if (isset($_REQUEST['locked']) && $locked = absint($_REQUEST['locked'])) {
			$messages[] = sprintf(_n('%s item not updated, somebody is editing it.', '%s items not updated, somebody is editing them.', $locked), number_format_i18n($locked));
		}

		if (isset($_REQUEST['deleted']) && $deleted = absint($_REQUEST['deleted'])) {
			$messages[] = sprintf(_n('Item permanently deleted.', '%s items permanently deleted.', $deleted), number_format_i18n($deleted));
		}

		if (isset($_REQUEST['trashed']) && $trashed = absint($_REQUEST['trashed'])) {
			$messages[] = sprintf(_n('Item moved to the Trash.', '%s items moved to the Trash.', $trashed), number_format_i18n($trashed));
			$ids        = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : 0;
			$messages[] = '<a href="' . esc_url( wp_nonce_url( "edit.php?post_type=$post_type&doaction=undo&action=untrash&ids=$ids", 'bulk-posts')) . '">' . __('Undo') . '</a>';
		}

		if (isset($_REQUEST['untrashed']) && $untrashed = absint($_REQUEST['untrashed'])) {
			$messages[] = sprintf(_n('Item restored from the Trash.', '%s items restored from the Trash.', $untrashed), number_format_i18n($untrashed ) );
		}

		if ($messages) {
			echo join(' ', $messages);
		}

		unset($messages);

		$_SERVER['REQUEST_URI'] = remove_query_arg(array('locked', 'skipped', 'updated', 'deleted', 'trashed', 'untrashed'), $_SERVER['REQUEST_URI']);

		echo '</p></div>';
	}
	?>

	<?php $list_table->views() ?>

	<form id="posts-filter" action="admin.php" method="get">
		<input type="hidden" name="page" value="<?= esc_attr($_GET['page']) ?>">
		<?php $list_table->search_box('Search', 'post') ?>

		<input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>">
		<input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>">
		<?php if (!empty($_REQUEST['show_sticky'])) { ?>
		<input type="hidden" name="show_sticky" value="1">
		<?php } ?>

		<?php $list_table->display() ?>
	</form>

	<div id="ajax-response"></div>
	<br class="clear">
</div>
