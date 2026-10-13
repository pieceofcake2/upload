<?php
App::uses('UploadBehavior', 'Upload.Model/Behavior');
App::uses('Folder', 'Utility');

class TestUpload extends CakeTestModel {

	public $useTable = 'uploads';

	public $actsAs = [
		'Upload.Upload' => [
			'photo' => [
				'thumbnailMethod' => '_bad_thumbnail_method_',
				'pathMethod' => '_bad_path_method_',
			]
		]
	];

}

class TestUploadTwo extends CakeTestModel {

	public $useTable = 'uploads';

	public $actsAs = [
		'Upload.Upload' => [
			'photo' => [
				'fields' => [
					'type' => 'type',
					'dir' => 'dir'
				],
				'mimetypes' => [
					'image/png',
					'image/jpeg',
					'image/gif'
				],
				'thumbnailSizes' => [
					'thumb' => '80h'
				]
			]
		]
	];

}

class UploadBehaviorTest extends CakeTestCase {

	public $fixtures = ['plugin.upload.upload'];

	public $TestUpload = null;

	public $TestUploadTwo = null;

	public $MockUpload = null;

	public $data = [];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp(): void {
		parent::setUp();
		$this->TestUpload = ClassRegistry::init('TestUpload');
		$this->TestUploadTwo = ClassRegistry::init('TestUploadTwo');
		$this->data['test_empty'] = [
			'photo' => ''
		];
		$this->data['test_ok'] = [
			'photo' => [
				'name' => 'Photo.png',
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
				'error' => UPLOAD_ERR_OK,
			]
		];
		$this->data['test_ok_bmp'] = [
			'photo' => [
				'name' => 'Photo.bmp',
				'tmp_name' => 'Photo.bmp',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/bmp',
				'size' => 8192,
				'error' => UPLOAD_ERR_OK,
			]
		];
		$this->data['test_update'] = [
			'id' => 1,
			'photo' => [
				'name' => 'NewPhoto.png',
				'tmp_name' => 'PhotoTmp.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
				'error' => UPLOAD_ERR_OK,
			]
		];
		$this->data['test_update_other_field'] = [
			'id' => 1,
			'other_field' => 'test',
			'photo' => []
		];
		$this->data['test_update_other_field_without_photo_set'] = [
			'id' => 1,
			'other_field' => 'test',
		];
		$this->data['test_remove'] = [
			'photo' => [
				'remove' => true,
			]
		];
	}

/**
 * Setup a MockUpload model with an attached behavior
 *
 * @param array $methods array of methods to mock
 * @return void
 */
	public function mockUpload($methods = []) {
		if (!is_array($methods)) {
			$methods = (array)$methods;
		}
		if (empty($methods)) {
			$methods = ['handleUploadedFile', 'unlink', '_getMimeType', '_createThumbnails'];
		}
		$this->MockUpload = $this->getMockBuilder(UploadBehavior::class)
			->onlyMethods($methods)
			->getMock();

		$this->MockUpload->setup($this->TestUpload, $this->TestUpload->actsAs['Upload.Upload']);
		$this->TestUpload->Behaviors->set('Upload', $this->MockUpload);

		$this->MockUpload->setup($this->TestUploadTwo, $this->TestUploadTwo->actsAs['Upload.Upload']);
		$this->TestUploadTwo->Behaviors->set('Upload', $this->MockUpload);
	}

/**
 * Call a protected method on an object
 *
 * @param Object $object object
 * @param string $name method to call
 * @param array $args arguments to pass to the method
 * @return mixed
 */
	public function protectedMethodCall($obj, $name, array $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown(): void {
		$folder = new Folder(TMP);
		$folder->delete(ROOT . DS . APP_DIR . DS . 'webroot' . DS . 'files' . DS . 'test_upload');
		$folder->delete(ROOT . DS . APP_DIR . DS . 'tmp' . DS . 'tests' . DS . 'path');
		ClassRegistry::flush();
		unset($this->TestUpload);
		unset($this->TestUploadTwo);
		parent::tearDown();
	}

	public function testSetup(): void {
		$this->mockUpload(['handleUploadedFile', 'unlink']);
		$this->assertEquals('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEquals('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);
	}

	public function testUploadSettings(): void {
		$this->mockUpload(['handleUploadedFile', 'unlink']);
		$this->assertEquals('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEquals('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);

		$this->TestUpload->uploadSettings('photo', 'thumbnailMethod', '_resizePhp');
		$this->assertEquals('_resizePhp', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEquals('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);

		$this->TestUpload->uploadSettings('photo', [
			'thumbnailMethod' => '_resizeImagick',
			'pathMethod' => '_getPathFlat',
		]);
		$this->assertEquals('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEquals('_getPathFlat', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);

		$this->TestUpload->uploadSettings('photo', ['pathMethod', 'thumbnailQuality'], ['_getPathPrimaryKey', 100]);
		$this->assertEquals('_resizeImagick', $this->MockUpload->settings['TestUpload']['photo']['thumbnailMethod']);
		$this->assertEquals('_getPathPrimaryKey', $this->MockUpload->settings['TestUpload']['photo']['pathMethod']);
		$this->assertEquals(100, $this->MockUpload->settings['TestUpload']['photo']['thumbnailQuality']);
	}

	public function testFileSize(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(true);
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertIsArray($result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEquals($this->data['test_ok']['photo']['size'], $newRecord['TestUpload']['size']);
	}

	public function testEmptyUpload(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('handleUploadedFile')->willReturn(true);
		$this->MockUpload->expects($this->never())->method('unlink');
		$result = $this->TestUpload->save($this->data['test_empty']);
		$this->assertIsArray($result);

		$id = $this->TestUpload->id;
		$newRecord = $this->TestUpload->findById($id);
		$expectedRecord = [
			'TestUpload' => [
				'id' => $id,
				'photo' => null,
				'dir' => null,
				'type' => null,
				'size' => null,
				'other_field' => null
			]
		];

		$this->assertEquals($expectedRecord, $newRecord);
	}

	public function testSimpleUpload(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(true);
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->with(
			$this->TestUpload,
			'photo',
			$this->data['test_ok']['photo']['tmp_name'],
			$this->MockUpload->settings['TestUpload']['photo']['path'] . 3 . DS . $this->data['test_ok']['photo']['name']
		);
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertIsArray($result);

		$id = $this->TestUpload->id;
		$newRecord = $this->TestUpload->findById($id);
		$expectedRecord = [
			'TestUpload' => [
				'id' => $id,
				'photo' => 'Photo.png',
				'dir' => 3,
				'type' => 'image/png',
				'size' => 8192,
				'other_field' => null
			]
		];

		$this->assertEquals($expectedRecord, $newRecord);
	}

/**
 * Tests Upload::save creates a new Upload record including
 * an upload of an PNG image file using the Upload.Upload behavior
 * with the default path and pathMethod (primaryKey)
 *
 * @return void
 **/
	public function testSaveSuccessPngDefaultPathAndPathMethod(): void {
		$this->mockUpload();
		$nextId = (1 + $this->TestUploadTwo->field('id', [], ['TestUploadTwo.id' => 'DESC']));
		$destinationDir = APP . 'webroot' . DS . 'files' . DS . 'test_upload_two' . DS . 'photo' . DS . $nextId . DS;

		$Upload = [
			'TestUploadTwo' => [
				'photo' => [
					'name' => 'image-png.png',
					'type' => 'image/png',
					'tmp_name' => 'image-png-tmp.png',
					'error' => UPLOAD_ERR_OK,
					'size' => 8123,
				]
			]
		];

		$this->MockUpload->expects($this->never())
			->method('unlink');

		$this->MockUpload->expects($this->once())
			->method('handleUploadedFile')
			->with(
					$this->equalTo($this->TestUploadTwo),
					$this->equalTo('photo'),
					$this->equalTo('image-png-tmp.png'),
					$this->equalTo($destinationDir . 'image-png.png')
			)
			->willReturn(true);

		$this->MockUpload->expects($this->once())
			->method('_createThumbnails')
			->with(
					$this->isInstanceOf('TestUploadTwo'),
					$this->equalTo('photo'),
					$this->equalTo($destinationDir),
					$this->equalTo($destinationDir)
			)
			->willReturn(true);

		$this->assertTrue(false !== $this->TestUploadTwo->save($Upload));
		$this->assertSame([], array_keys($this->TestUploadTwo->validationErrors));

		$this->assertSame('image-png.png', $this->TestUploadTwo->field('photo', ['TestUploadTwo.id' => $nextId]));
		$this->assertSame('image/png', $this->TestUploadTwo->field('type', ['TestUploadTwo.id' => $nextId]));
		$this->assertSame((string)$nextId, $this->TestUploadTwo->field('dir', ['TestUploadTwo.id' => $nextId]));
	}

	public function testSaveDirEscaped(): void {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['pathMethod'] = 'random';
		$this->mockUpload(['handleUploadedFile', 'unlink', '_getMimeType', '_createThumbnails', '_getPathRandom']);

		$DS = '\\';
		$randomPath = '01' . $DS . '02' . $DS . '03';

		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(true);
		$this->MockUpload->expects($this->once())->method('_getPathRandom')->willReturn($randomPath);

		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertIsArray($result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);

		$this->assertSame($randomPath, $newRecord['TestUpload']['dir']);
	}

	public function testDeleteOnUpdate(): void {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['deleteOnUpdate'] = true;
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(true);
		$this->MockUpload->expects($this->once())->method('unlink')->willReturn(true);

		$existingRecord = $this->TestUpload->findById($this->data['test_update']['id']);
		$this->MockUpload->expects($this->once())->method('unlink')->with(
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $existingRecord['TestUpload']['dir'] . DS . $existingRecord['TestUpload']['photo']
		);
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->with(
			$this->TestUpload,
			'photo',
			$this->data['test_update']['photo']['tmp_name'],
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $this->data['test_update']['id'] . DS . $this->data['test_update']['photo']['name']
		);
		$result = $this->TestUpload->save($this->data['test_update']);
		$this->assertIsArray($result);
	}

	public function testDeleteOnUpdateWithoutNewUpload(): void {
		$this->TestUpload->actsAs['Upload.Upload']['photo']['deleteOnUpdate'] = true;
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field']);
		$this->assertIsArray($result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEquals($this->data['test_update_other_field']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	public function testUpdateWithoutNewUpload(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field']);
		$this->assertIsArray($result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEquals($this->data['test_update_other_field']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	public function testUpdateWithoutNewUploadWithoutFieldSet(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->never())->method('handleUploadedFile');
		$result = $this->TestUpload->save($this->data['test_update_other_field_without_photo_set']);
		$this->assertIsArray($result);
		$newRecord = $this->TestUpload->findById($this->TestUpload->id);
		$this->assertEquals($this->data['test_update_other_field_without_photo_set']['other_field'], $newRecord['TestUpload']['other_field']);
	}

	public function testRuntimeConfigIsResetBetweenSaves(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');

		// We'll save the record twice using the same model instance,
		// but handleUploadedFile should only be called once, on the first save.
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(true);
		$this->TestUpload->save($this->data['test_update']);
		$runtime = $this->TestUpload->Behaviors->Upload->runtime;
		$this->assertIsArray($runtime['TestUpload']['photo']);

		$this->TestUpload->read(null, $this->data['test_update']['id']);

		// intentionally don't pass an uploaded file in save data
		$this->TestUpload->save();
		$runtime = $this->TestUpload->Behaviors->Upload->runtime;
		$this->assertFalse(isset($runtime['TestUpload']['photo']));
	}

	public function testUnlinkFileOnDelete(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('unlink')->willReturn(true);
		$existingRecord = $this->TestUpload->findById($this->data['test_update']['id']);
		$this->MockUpload->expects($this->once())->method('unlink')->with(
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $existingRecord['TestUpload']['dir'] . DS . $existingRecord['TestUpload']['photo']
		);
		$result = $this->TestUpload->delete($this->data['test_update']['id']);
		$this->assertTrue($result);
		$this->assertEmpty($this->TestUpload->findById($this->data['test_update']['id']));
	}

	public function testKeepFilesOnDelete(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(true);
		$this->MockUpload->expects($this->never())->method('unlink');
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->with(
			$this->TestUpload,
			'photo',
			$this->data['test_ok']['photo']['tmp_name'],
			$this->MockUpload->settings['TestUpload']['photo']['path'] . 3 . DS . $this->data['test_ok']['photo']['name']
		);
		$result = $this->TestUpload->save($this->data['test_ok']);
		$this->assertIsArray($result);
		$id = $this->TestUpload->id;

		$existingRecord = $this->TestUpload->findById($id);
		$this->TestUpload->uploadSettings('photo', 'keepFilesOnDelete', true);
		$this->MockUpload->expects($this->never())->method('unlink');

		$result = $this->TestUpload->delete($id);
		$this->assertTrue($result);
		$this->assertEmpty($this->TestUpload->findById($id));
	}

	public function testDeleteFileOnTrueRemoveSave(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('unlink')->willReturn(true);

		$data = [
			'id' => 1,
			'photo' => [
				'remove' => true
			]
		];

		$existingRecord = $this->TestUpload->findById($data['id']);
		$this->MockUpload->expects($this->once())->method('unlink')->with(
			$this->MockUpload->settings['TestUpload']['photo']['path'] . $existingRecord['TestUpload']['dir'] . DS . $existingRecord['TestUpload']['photo']
		);
		$result = $this->TestUpload->save($data);
		$this->assertIsArray($result);
	}

	public function testKeepFileOnFalseRemoveSave(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');

		$data = [
			'id' => 1,
			'photo' => [
				'remove' => false
			]
		];

		$existingRecord = $this->TestUpload->findById($data['id']);
		$result = $this->TestUpload->save($data);
		$this->assertIsArray($result);
	}

	public function testKeepFileOnNullRemoveSave(): void {
		$this->mockUpload();
		$this->MockUpload->expects($this->never())->method('unlink');

		$data = [
			'id' => 1,
			'photo' => [
				'remove' => null
			]
		];

		$existingRecord = $this->TestUpload->findById($data['id']);
		$result = $this->TestUpload->save($data);
		$this->assertIsArray($result);
	}

/**
 * Test file move exception
 *
 * @return void
 **/
	public function testMoveFileExecption(): void {
		$this->expectException(UploadException::class);
		$this->mockUpload(['handleUploadedFile']);
		$this->MockUpload->expects($this->once())->method('handleUploadedFile')->willReturn(false);
		$result = $this->TestUpload->save($this->data['test_ok']);
	}

/**
 * Test validation rules
 *
 * @param string $rule rule to test
 * @param array $data validation data
 * @param array $record record to test
 * @return void
 * @dataProvider providerTestValidationRules
 **/
	public function testValidationRules($rule, $data, $record = []): void {
		$this->TestUpload->validate = [
			'photo' => [
				$rule => [
					'rule' => $rule,
					'message' => $rule
				],
			]
		];

		$data = array_merge([
			'photo' => array_merge([
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
			], $data
		), $record]);

		$this->__testInvalidValidationRule($rule, $data);
		$this->__testOkAndRemoveCasesValidate();
	}

	public function providerTestValidationRules(): array {
		return [
			['isUnderPhpSizeLimit', ['error' => UPLOAD_ERR_INI_SIZE]],
			['isUnderFormSizeLimit', ['error' => UPLOAD_ERR_FORM_SIZE]],
			['isCompletedUpload', ['error' => UPLOAD_ERR_PARTIAL]],
			['isFileUpload', ['error' => UPLOAD_ERR_NO_FILE]],
			['isFileUploadOrHasExistingValue', ['error' => UPLOAD_ERR_NO_FILE], ['id' => 2]],
			['isFileUploadOrHasExistingValue', ['error' => UPLOAD_ERR_NO_FILE], ['id' => '']],
			['isFileUploadOrHasExistingValue', ['error' => UPLOAD_ERR_NO_FILE]],
		];
	}

/**
 * Test ignorable validation rules
 *
 * @param string $rule rule to test
 * @param array $data validation data
 * @param array $record record to test
 * @return void
 * @dataProvider providerTestIgnorableValidationRules
 **/
	public function testIgnorableValidationRules($rule, $data, $record = []): void {
		$this->TestUpload->validate = [
			'photo' => [
				$rule => [
					'rule' => $rule,
					'message' => $rule
				],
			]
		];

		$data = array_merge([
			'photo' => array_merge([
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
			], $data
		), $record]);
		$this->__testInvalidValidationRule($rule, $data);

		$this->TestUpload->validate = ['photo' => [
			$rule => ['rule' => [$rule, false],
		]]];
		$data = ['photo' => ['error' => UPLOAD_ERR_NO_FILE]];
		$this->__testSkipValidationRule($rule, $data);
		$this->__testOkAndRemoveCasesValidate();
	}

	public function providerTestIgnorableValidationRules(): array {
		return [
			['tempDirExists', ['error' => UPLOAD_ERR_NO_TMP_DIR]],
			['isSuccessfulWrite', ['error' => UPLOAD_ERR_CANT_WRITE]],
			['noPhpExtensionErrors', ['error' => UPLOAD_ERR_EXTENSION]],
		];
	}

/**
 * Test changing configuration when running validation rules
 *
 * @param string $rule rule to test
 * @return void
 * @dataProvider providerTestChangeConfigurationValidationRules
 **/
	public function testChangeConfigurationValidationRules($rule): void {
		$this->TestUpload->validate = [
			'photo' => [
				$rule => [
					'rule' => $rule,
					'message' => $rule
				],
			]
		];

		$this->__testInvalidValidationRule($rule, $this->data['test_ok']);

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'photo' => [
				'path' => TMP
			]
		]);

		$this->__testOkCaseValidates();
		$this->__testRemoveCaseValidates();
	}

	public function providerTestChangeConfigurationValidationRules(): array {
		return [
			['isWritable'],
			['isValidDir'],
		];
	}

/**
 * This simulates the case where we are uploading no file
 * to an existing record, which DOES have an existing value.
 *
 * @return void
 **/
	public function testIsFileUploadOrHasExistingValueEditingWithExistingValue(): void {
		$this->TestUpload->validate = [
			'photo' => [
				'isFileUploadOrHasExistingValue' => [
					'rule' => 'isFileUploadOrHasExistingValue',
					'message' => 'isFileUploadOrHasExistingValue'
				],
			]
		];

		// Fixture record #1 has an existing value.
		$data = [
			'id' => 1,
			'photo' => [
				'tmp_name' => 'Photo.png',
				'dir' => '/tmp/php/file.tmp',
				'type' => 'image/png',
				'size' => 8192,
				'error' => UPLOAD_ERR_NO_FILE,
			]
		];
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEquals(0, count($this->TestUpload->validationErrors));

		$this->__testOkAndRemoveCasesValidate();
	}

	public function testIsValidMimeType(): void {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'photo' => [
				'mimetypes' => ['image/bmp', 'image/jpeg']
			]
		]);

		$this->TestUpload->validate = [
			'photo' => [
				'isValidMimeType' => [
					'rule' => 'isValidMimeType',
					'message' => 'isValidMimeType'
				],
			]
		];

		$this->__testInvalidValidationRule('isValidMimeType', $this->data['test_ok']);

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'photo' => [
				'mimetypes' => ['image/png', 'image/jpeg']
			]
		]);

		$this->__testOkAndRemoveCasesValidate();

		$this->TestUpload->validate = [
			'photo' => [
				'isValidMimeType' => [
					'rule' => ['isValidMimeType', 'image/png'],
					'message' => 'isValidMimeType',
				],
			]
		];

		$this->__testOkCaseValidates();

		$this->TestUpload->validate = ['photo' => [
			'isValidMimeType' => ['rule' => ['isValidMimeType', 'image/png', false],
		]]];
		$data = ['photo' => ['error' => UPLOAD_ERR_NO_FILE]];
		$this->__testSkipValidationRule('isValidMimeType', $data);
	}

	public function testIsValidExtension(): void {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'photo' => [
				'extensions' => ['jpeg', 'bmp']
			]
		]);

		$this->TestUpload->validate = [
			'photo' => [
				'isValidExtension' => [
					'rule' => 'isValidExtension',
					'message' => 'isValidExtension'
				],
			]
		];

		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->set($this->data['test_ok_bmp']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEquals(0, count($this->TestUpload->validationErrors));

		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'photo'
		]);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = ['isValidExtension', 'jpg'];
		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = ['isValidExtension', ['jpg']];
		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = ['isValidExtension', ['jpg', 'bmp']];
		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$this->TestUpload->validate['photo']['isValidExtension']['rule'] = ['isValidExtension', ['jpg', 'bmp', 'png']];
		$this->__testOkCaseValidates();

		$this->TestUpload->validate = [
			'photo' => [
				'isFileUpload' => [
					'rule' => 'isFileUpload',
					'message' => 'isFileUpload'
				],
				'isValidExtension' => [
					'rule' => ['isValidExtension', ['jpg']],
					'message' => 'isValidExtension'
				],
			]
		];

		$this->__testInvalidValidationRule('isValidExtension', $this->data['test_ok']);

		$data = $this->data['test_ok'];
		$data['photo']['name'] = 'Photo.jpeg';
		$this->__testInvalidValidationRule('isValidExtension', $data);

		$this->__testRemoveCaseValidates();

		$this->TestUpload->validate = ['photo' => [
			'isValidExtension' => ['rule' => ['isValidExtension', ['jpeg', 'bmp'], false],
		]]];
		$data = ['photo' => ['error' => UPLOAD_ERR_NO_FILE]];
		$this->__testSkipValidationRule('isValidExtension', $data);
	}

	public function testIsImage(): void {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'photo' => [
				'mimetypes' => ['image/bmp', 'image/jpeg']
			]
		]);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isImage', [
			'image/bmp'
		]);
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isImage', [
			'image/jpeg'
		]);
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isImage', [
			'application/zip'
		]);
		$this->assertFalse($result);
	}

	public function testIsMedia(): void {
		$this->TestUpload->Behaviors->detach('Upload.Upload');
		$this->TestUpload->Behaviors->attach('Upload.Upload', [
			'pdf_file' => [
				'mimetypes' => ['application/pdf', 'application/postscript']
			]
		]);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', [
			'application/pdf'
		]);
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', [
			'application/postscript'
		]);
		$this->assertTrue($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', [
			'application/zip'
		]);
		$this->assertFalse($result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_isMedia', [
			'image/jpeg'
		]);
		$this->assertFalse($result);
	}

	public function testGetPathFlat(): void {
		$basePath = 'tests' . DS . 'path' . DS . 'flat' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathFlat', [
			$this->TestUpload, 'photo', TMP . $basePath
		]);

		$this->assertIsString($result);
		$this->assertEquals(0, strlen($result));
	}

	public function testGetPathPrimaryKey(): void {
		$this->TestUpload->id = 5;
		$basePath = 'tests' . DS . 'path' . DS . 'primaryKey' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathPrimaryKey', [
			$this->TestUpload, 'photo', TMP . $basePath
		]);

		$this->assertIsInt($result);
		$this->assertEquals(1, strlen($result));
		$this->assertEquals($result, $this->TestUpload->id);
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	public function testGetPathRandom(): void {
		$basePath = 'tests' . DS . 'path' . DS . 'random' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathRandom', [
			$this->TestUpload, 'photo', TMP . $basePath
		]);

		$this->assertIsString($result);
		$this->assertEquals(8, strlen($result));
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	public function testGetPathRandomCombined(): void {
		$this->TestUpload->id = 5;
		$basePath = 'tests' . DS . 'path' . DS . 'random' . DS;
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_getPathRandomCombined', [
			$this->TestUpload, 'photo', TMP . $basePath
		]);

		$this->assertIsString($result);
		$this->assertEquals(9 + strlen($this->TestUpload->id), strlen($result));
		$this->assertEquals(substr($result, 0, strlen($this->TestUpload->id)), $this->TestUpload->id);
		$this->assertTrue(is_dir(TMP . $basePath . $result));
	}

	public function testReplacePath(): void {
		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_path', [
			$this->TestUpload, 'photo', ['path' => 'webroot{DS}files/{model}\\{field}{DS}']
		]);

		$this->assertIsString($result);
		$this->assertEquals(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_path', [
			$this->TestUpload, 'photo', ['path' => 'webroot{DS}files//{size}/{model}\\{field}{DS}{geometry}///']
		]);

		$this->assertIsString($result);
		$this->assertEquals(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . '{size}' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . '{geometry}' . DIRECTORY_SEPARATOR, $result);

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_path', [
			$this->TestUpload, 'photo', ['isThumbnail' => false, 'path' => 'webroot{DS}files//{size}/{model}\\\\{field}{DS}{geometry}///']
		]);

		$this->assertIsString($result);
		$this->assertEquals(WWW_ROOT . 'files' . DIRECTORY_SEPARATOR . 'test_upload' . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR, $result);
	}

	public function testPrepareFilesForDeletion(): void {
		$this->TestUpload->actsAs['Upload.Upload'] = [
			'photo' => [
				'thumbnailSizes' => [
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				],
				'fields' => [
					'dir' => 'dir'
				]
			]
		];
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->willReturn('image/png');

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', [
			$this->TestUpload, 'photo',
			['TestUpload' => ['id' => 1, 'dir' => '1', 'photo' => 'Photo.png']],
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		]);

		$this->assertIsArray($result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(4, count($result['TestUpload']));
	}

	public function testPrepareFilesForDeletionWithoutDirDataFieldWithPrimaryKeyPathMethod(): void {
		$this->TestUpload->actsAs['Upload.Upload'] = [
			'photo' => [
				'pathMethod' => 'primaryKey',
				'thumbnailSizes' => [
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				],
				'fields' => [
					'dir' => false
				]
			]
		];
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->willReturn('image/png');

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', [
			$this->TestUpload, 'photo',
			['TestUpload' => ['id' => 1, 'photo' => 'Photo.png']],
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		]);
		$this->assertIsArray($result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(4, count($result['TestUpload']));

		$basePath	= $this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']['path'];
		$primaryKey = 1;
		$this->assertEquals($result['TestUpload'][0], sprintf('%s%d' . DS . 'Photo.png', $basePath, $primaryKey));
		$this->assertEquals($result['TestUpload'][1], sprintf('%s%d' . DS . 'xvga_Photo.png', $basePath, $primaryKey));
		$this->assertEquals($result['TestUpload'][2], sprintf('%s%d' . DS . 'vga_Photo.png', $basePath, $primaryKey));
		$this->assertEquals($result['TestUpload'][3], sprintf('%s%d' . DS . 'thumb_Photo.png', $basePath, $primaryKey));
	}

	public function testPrepareFilesForDeletionWithoutDirDataFieldWithFlagPathMethod(): void {
		$this->TestUpload->actsAs['Upload.Upload'] = [
			'photo' => [
				'pathMethod' => 'flat',
				'thumbnailSizes' => [
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				],
				'fields' => [
					'dir' => false
				]
			]
		];
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->willReturn('image/png');

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', [
			$this->TestUpload, 'photo',
			['TestUpload' => ['id' => 1, 'photo' => 'Photo.png']],
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		]);

		$this->assertIsArray($result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(4, count($result['TestUpload']));

		$basePath	= $this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']['path'];
		$primaryKey = 1;
		$this->assertEquals($result['TestUpload'][0], sprintf('%sPhoto.png', $basePath));
		$this->assertEquals($result['TestUpload'][1], sprintf('%sxvga_Photo.png', $basePath));
		$this->assertEquals($result['TestUpload'][2], sprintf('%svga_Photo.png', $basePath));
		$this->assertEquals($result['TestUpload'][3], sprintf('%sthumb_Photo.png', $basePath));
	}

	public function testPrepareFilesForDeletionWithThumbnailType(): void {
		$this->TestUpload->actsAs['Upload.Upload'] = [
			'photo' => [
				'thumbnailSizes' => [
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				],
				'fields' => [
					'dir' => 'dir'
				],
				'thumbnailType' => 'jpg'
			]
		];
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->willReturn('image/png');

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', [
			$this->TestUpload, 'photo',
			['TestUpload' => ['id' => 1, 'dir' => '1', 'photo' => 'Photo.png']],
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		]);

		$this->assertIsArray($result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(4, count($result['TestUpload']));
	}

	public function testPrepareFilesForDeletionWithMediaFileAndFalseThumbnailType(): void {
		$this->TestUpload->actsAs['Upload.Upload'] = [
			'photo' => [
				'thumbnailSizes' => [
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				],
				'fields' => [
					'dir' => 'dir'
				],
				'thumbnailType' => false
			]
		];
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->willReturn('application/pdf');

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', [
			$this->TestUpload, 'photo',
			['TestUpload' => ['id' => 1, 'dir' => '1', 'photo' => 'Photo.pdf']],
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		]);

		$this->assertIsArray($result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(4, count($result['TestUpload']));
	}

	public function testPrepareFilesForDeletionWithMediaFile(): void {
		$this->TestUpload->actsAs['Upload.Upload'] = [
			'photo' => [
				'thumbnailSizes' => [
					'xvga' => '1024x768',
					'vga' => '640x480',
					'thumb' => '80x80'
				],
				'fields' => [
					'dir' => 'dir'
				]
			]
		];
		$this->mockUpload();
		$this->MockUpload->expects($this->once())->method('_getMimeType')->willReturn('application/pdf');

		$result = $this->protectedMethodCall($this->TestUpload->Behaviors->Upload, '_prepareFilesForDeletion', [
			$this->TestUpload, 'photo',
			['TestUpload' => ['id' => 1, 'dir' => '1', 'photo' => 'Photo.pdf']],
			$this->TestUpload->Behaviors->Upload->settings['TestUpload']['photo']
		]);

		$this->assertIsArray($result);
		$this->assertEquals(1, count($result));
		$this->assertEquals(4, count($result['TestUpload']));
	}

	private function __testOkAndRemoveCasesValidate() {
		$this->__testOkCaseValidates();
		$this->__testRemoveCaseValidates();
	}

	private function __testValidates($data) {
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEquals(0, count($this->TestUpload->validationErrors));
	}

	private function __testOkCaseValidates() {
		$this->__testValidates($this->data['test_ok']);
	}

	private function __testRemoveCaseValidates() {
		$this->TestUpload->set($this->data['test_remove']);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEquals(0, count($this->TestUpload->validationErrors));
	}

	private function __testSkipValidationRule($ruleName, $data) {
		$this->TestUpload->set($data);
		$this->assertTrue($this->TestUpload->validates());
		$this->assertEquals(0, count($this->TestUpload->validationErrors));
	}

	private function __testInvalidValidationRule($ruleName, $data) {
		$this->TestUpload->set($data);
		$this->assertFalse($this->TestUpload->validates());
		$this->assertEquals(1, count($this->TestUpload->validationErrors));
		$this->assertEquals($ruleName, current($this->TestUpload->validationErrors['photo']));
	}

}
