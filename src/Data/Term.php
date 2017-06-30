<?php
namespace WPGraphQL\Extensions\Fieldmanager\Data;

class Term extends \Fieldmanager_Context_Term {

	public function resolve() {
		return parent::load();
	}

}
