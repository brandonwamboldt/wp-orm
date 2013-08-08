<?php

/**
 * Plugin Name: WordPress ORM
 * Plugin URI: http://wordpress.org/extend/plugins/wp-orm/
 * Description: This is a plugin to provide basic ORM functionality and object oriented models for WordPress
 * Version: 1.0
 * Author: Brandon Wamboldt
 * Author URI: http://brandonwamboldt.ca/
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 */

require 'lib/BaseModel.php';
require 'lib/Query.php';
require 'models/Comment.php';
require 'models/Post.php';
require 'models/Page.php';
require 'models/User.php';