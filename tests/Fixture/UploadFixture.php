<?php
/* Upload Fixture generated on: 2010-08-11 21:08:00 : 1281575760 */
class UploadFixture extends CakeTestFixture {

	public $name = 'Upload';

	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'],
		'photo' => ['type' => 'string', 'null' => true, 'default' => null],
		'dir' => ['type' => 'string', 'null' => true, 'default' => null],
		'type' => ['type' => 'string', 'null' => true, 'default' => null],
		'size' => ['type' => 'integer', 'null' => true, 'default' => null],
		'other_field' => ['type' => 'string', 'null' => true, 'default' => null],
		'indexes' => ['PRIMARY' => ['column' => 'id', 'unique' => 1]],
		'tableParameters' => [
			'charset' => 'utf8',
			'collate' => 'utf8_general_ci'
		],
	];

	public $records = [
		[
			'id' => 1,
			'photo' => 'Photo.png',
			'dir' => '1',
			'type' => 'image/png',
			'size' => 8192
		],
		[
			// Intentionally empty record, for testing isFileUploadOrHasExistingValue validation
			'id' => 2,
			'photo' => '',
			'dir' => '',
			'type' => '',
			'size' => 0
		],
	];

}
