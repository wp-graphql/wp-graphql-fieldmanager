<?php
namespace WPGraphQL\Extensions\Fieldmanager\Data;

class Post extends \Fieldmanager_Context_Post {

	public function resolve() {
		return parent::load();
	}

}
