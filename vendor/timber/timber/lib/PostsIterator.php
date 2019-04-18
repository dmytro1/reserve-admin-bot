<?php

namespace Timber;

/**
 * Class PostsIterator
 */
class PostsIterator extends \ArrayIterator {

	public function current() {
		global $post;
		$post = parent::current();
		return $post;
	}

}
