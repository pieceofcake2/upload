<?php
App::uses('FileImportBehavior', 'Upload.Model/Behavior');
App::uses('Folder', 'Utility');

class FileImportUpload extends CakeTestModel
{
    public $useTable = 'uploads';

    public $actsAs = [
        'Upload.FileImport' => [
            'photo' => [
                'path' => '{ROOT}tmp{DS}tests{DS}file_import{DS}{field}{DS}',
                'thumbnailMethod' => 'php',
                'thumbnailSizes' => [
                    'thumb' => '[20x20]',
                ],
            ],
        ],
    ];
}

class FileImportBehaviorTest extends CakeTestCase
{
    public $fixtures = ['plugin.upload.upload'];

    /**
     * @var FileImportUpload
     */
    public $FileImportUpload;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->FileImportUpload = ClassRegistry::init('FileImportUpload');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $folder = new Folder();
        $folder->delete(TMP . 'tests' . DS . 'file_import');
        unset($this->FileImportUpload);
        ClassRegistry::flush();

        parent::tearDown();
    }

    public function testImportFileFromDisk(): void
    {
        $sourceDir = TMP . 'tests' . DS . 'file_import' . DS . 'source' . DS;
        mkdir($sourceDir, 0777, true);
        $source = $sourceDir . 'import.jpg';
        imagejpeg(imagecreatetruecolor(80, 40), $source);

        $this->assertInstanceOf(FileImportBehavior::class, $this->FileImportUpload->Behaviors->FileImport);

        $result = $this->FileImportUpload->save([
            'photo' => [
                'name' => 'import.jpg',
                'tmp_name' => $source,
                'type' => 'image/jpeg',
                'size' => filesize($source),
                'error' => UPLOAD_ERR_OK,
            ],
        ]);
        $this->assertIsArray($result);

        $id = $this->FileImportUpload->id;
        $dir = TMP . 'tests' . DS . 'file_import' . DS . 'photo' . DS . $id . DS;
        $this->assertFileDoesNotExist($source);
        $this->assertFileExists($dir . 'import.jpg');
        $this->assertSame([20, 20], array_slice(getimagesize($dir . 'thumb_import.jpg'), 0, 2));

        $record = $this->FileImportUpload->findById($id);
        $this->assertSame('import.jpg', $record['FileImportUpload']['photo']);
        $this->assertSame('image/jpeg', $record['FileImportUpload']['type']);
        $this->assertEquals($id, $record['FileImportUpload']['dir']);
    }
}
