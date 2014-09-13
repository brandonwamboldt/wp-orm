WordPress ORM
=============

WordPress ORM is a small library that adds a basic ORM into WordPress, which is easily extendable and includes models for core WordPress models such as posts, pages, comments and users. It's designed to allow you to easily add new models other than custom post types, and not have to write a lot of manual SQL.

Installation
------------

While you can install and activate it like a normal plugin, I'd recommend putting it in the `/wp-content/mu-plugins` folder and adding a script called `wp-orm.php` to load the main plugin file (only top level `.php` files are loaded from `mu-plugins`).

`wp-orm.php`:

```php
<?php require 'wp-orm/wp-orm.php';
```

Examples
--------

#### Get 5 published pages, ordered by post title.

```php
use WordPress\ORM\Model\Page;

$pages = Page::query()
	->limit(5)
	->where('post_status', 'publish')
	->sort_by('post_title')
	->order('ASC')
	->find();
```

#### Find a  user by their login

```php
use WordPress\ORM\Model\User;

$user = User::find_one_by('user_login', 'brandon');

echo $user->get_user_login();

print_r($user->to_array());
```

#### Example of a more complex query

```php
use WordPress\ORM\Model\Post;

$posts = Post::query()
	->limit(15)
	->offset(0)
	->where_all(['post_status' => 'publish', 'post_type' => 'post'])
	->where_like('post_title', '%Hello world%')
	->sort_by('post_title')
	->order('ASC')
	->find();
```

#### Updating a model

```php
use WordPress\ORM\Model\Post;

$post = Post::find_one(1204);
$post->set_post_title('What an amazing post!');
$post->save();
```

#### Meta data

Users, posts, pages and comments all support meta data.

```php
$post = Post::find_one(1337);
$post->get_metadata('_edit_lock');
$post->update_metadata('_edit_lock', '');
$post->delete_metadata('_edit_lock');
```

Meta data is saved immediately using WordPress meta data functions under the hood. Calling `save()` is not needed.

Custom Models
-------------

```php
<?php

namespace WordPress\ORM;

class Venue extends BaseModel
{
	protected $id;
	protected $venue_title;
	protected $description;
	protected $now_playing;
	protected $location;
	protected $avg_rating;

	public static function get_primary_key()
	{
		return 'id';
	}

	public static function get_table()
	{
		return 'wp_venues';
	}

	public static function get_searchable_fields()
	{
		return ['venue_title', 'description', 'now_playing'];
	}
}
```

You can now use this venue, persist it, and use the custom query DSL shown above to query it.

Model Methods
-------------

##### Model::get_table()

This is a static method that you must define in your models, and should return the table to persist data to.

##### Model::get_searchable_fields()

This is a static method that you must define in your models, and should return an array of properties to search when doing a search query.

##### Model::get_primary_key()

Return's the property used as a primary key. Defaults to `id`.

##### Model::create(array $properties)

Create a new model from an array of properties.

##### Model::find_one_by(string $property, mixed $value)

Find a single model with the specified property value.

##### Model::find_one(integer $id)

Find a single model who's primary key is equal to the given ID.

##### Model::query()

Return a new `WordPress\ORM\Query` object.

##### Model::all()

Return every single model in the database.

##### $model->primary_key()

Return the model's primary key (the value, not the property name).

##### $model->to_array()

Return all of the model's properties as an array.

##### $model->flatten_props(array $props)

Call right before `save()`, should flatten any objects in the properties into strings so they can be persisted. Defaults to flattening `DateTime` objects into a timestamp and arrays into a serialized array.

##### $model->save()

Save your model to the database. Creates a new row if the model doesn't have an ID, or updates an existing row if their is an ID.

##### $model->delete()

Delete the model from the database. Returns `true` if it was successful or `false` if it was not.

ORM Queries
-----------

Below are the functions you have access to after you call the `Model::query()` function.

##### $query->limit(integer $limit)

Limits the number of results returned using an SQL `LIMIT` clause.

##### $query->offset(integer $offset)

Offset the results returned, used with pagination. Uses the SQL `OFFSET` clause.

##### $query->sort_by(string $property)

Sort results by the specified property. Can also be a MySQL function such as `RAND()`.

##### $query->order(string $order)

Order the results in the given order. Can be one of `ASC` or `DESC`.

##### $query->search(string $search_term)

Limit results to items matching the given search term. Searches the properties returned by `Model::get_searchable_fields`.

##### $query->where(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property = '$value'`. `$value` is automatically escaped.

##### $query->where_not(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property != '$value'`. `$value` is automatically escaped.

##### $query->where_like(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property LIKE '$value'`. `$value` is automatically escaped.

##### $query->where_not_like(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property NOT LIKE '$value'`. `$value` is automatically escaped.

##### $query->where_lt(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property < '$value'`. `$value` is automatically escaped.

##### $query->where_lte(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property <= '$value'`. `$value` is automatically escaped.

##### $query->where_gt(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property > '$value'`. `$value` is automatically escaped.

##### $query->where_gte(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property >= '$value'`. `$value` is automatically escaped.

##### $query->where_in(string $column, array $in)

Limit results to items where the given column is one of the given values. Equivalent to ` WHERE $property IN ('value1', 'value2')` where `value1` and `value2` are values in the array.

##### $query->where_not_in(string $column, array $in)

Limit results to items where the given column is not one of the given values. Equivalent to ` WHERE $property NOT IN ('value1', 'value2')` where `value1` and `value2` are values in the array.

##### $query->where_any(array $where)

Limit results to items that match any of the property/value pairs given in the array. Must match at least one.

##### $query->where_all(array $where)

Limit results to items that match all of the property/value pairs given in the array.

Actions & Filters
-----------------

#### wporm_query($sql, $model_class)

Manipulate the raw SQL query created by the Query class.

```php
add_filter('wporm_query', function($sql, $model_class) {
	if ($model_class == 'WordPress\ORM\Model\Page') {
		$sql = str_replace('wp_posts', 'wp2_posts', $sql);
	}

	return $sql;
}, 10, 2);
```

#### wporm_count_query($sql, $model_class)

Manipulate the raw SQL query created by the Query class (the row count variation).

```php
add_filter('wporm_count_query', function($sql, $model_class) {
	if ($model_class == 'WordPress\ORM\Model\Page') {
		$sql = str_replace('wp_posts', 'wp2_posts', $sql);
	}

	return $sql;
}, 10, 2);
```

License
-------

This code is licensed under the MIT license.
