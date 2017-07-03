<?php
namespace WPGraphQL\Extensions\Fieldmanager\Type\Enum;

use WPGraphQL\Type\WPEnumType;

class AddMorePositionEnum extends WPEnumType {

	public function __construct() {
		$config = [
			'name' => 'addMorePosition',
			'values' => [
				[
					'name' => 'TOP',
					'value' => 'top',
				],
				[
					'name' => 'BOTTOM',
					'value' => 'bottom',
				],
			],
		];
		parent::__construct( $config );
	}

}
