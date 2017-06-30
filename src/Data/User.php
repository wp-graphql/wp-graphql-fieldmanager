<?php
namespace WPGraphQL\Extensions\Fieldmanager\Data;

class User extends \Fieldmanager_Context_User {

	public function resolve() {
		return parent::load();
	}

}
