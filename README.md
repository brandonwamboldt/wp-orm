WordPress ORM
=============

WordPress ORM is a small library that adds a basic ORM into WordPress, which is easily extendable and includes models for core WordPress models such as posts, pages, comments and users. It's designed to allow you to easily add new models other than custom post types, and not have to write a lot of manual SQL.

Examples
--------

#### Get 5 published pages, ordered by post title.

```
use WordPress\Orm\Model\Page;

$pages = Page::query()
	->limit(5)
	->where('post_status', 'publish')
	->sort_by('post_title')
	->order('ASC')
	->find();
```

#### Find a  user by their login

```
use WordPress\Orm\Model\Page;

$user = User::find_one_by('user_login', 'brandon');

echo $user->get_user_login();

print_r($user->to_array());
```

#### Example of a more complex query

```
use WordPress\Orm\Model\Post;

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

```
use WordPress\Orm\Model\Post;

$post = Post::find_one(1204);
$post->set_post_title('What an amazing post!');
$post->save();
```

#### Meta data

Users, posts, pages and comments all support meta data.

```
$post = Post::find_one(1337);
$post->get_metadata('_edit_lock');
$post->update_metadata('_edit_lock', '');
$post->delete_metadata('_edit_lock');
```

Meta data is saved immediately using WordPress meta data functions under the hood. Calling `save()` is not needed.

Custom Models
-------------

```
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

ORM Queries
-----------

Below are the functions you have access to after you call the `Model::query()` function.

##### #limit(integer $limit)

Limits the number of results returned using an SQL `LIMIT` clause.

##### #offset(integer $offset)

Offset the results returned, used with pagination. Uses the SQL `OFFSET` clause.

##### #sort_by(string $property)

Sort results by the specified property

##### #order(string $order)

Order the results in the given order. Can be one of `ASC` or `DESC`.

##### #search(string $search_term)

Limit results to items matching the given search term. Searches the properties returned by `Model::get_searchable_fields`.

##### #where(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property = '$value'`. `$value` is automatically escaped.

##### #where_not(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property != '$value'`. `$value` is automatically escaped.

##### #where_like(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property LIKE '$value'`. `$value` is automatically escaped.

##### #where_not_like(string $property, string $value)

Add a parameter to the where clause. Equivalent to ` WHERE $property NOT LIKE '$value'`. `$value` is automatically escaped.

##### #where_any(array $where)

Limit results to items that match any of the property/value pairs given in the array. Must match at least one.

##### #where_all(array $where)

Limit results to items that match all of the property/value pairs given in the array.