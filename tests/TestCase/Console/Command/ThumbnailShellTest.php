<?php
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Folder', 'Utility');
App::uses('ThumbnailShell', 'Upload.Console/Command');

class ThumbnailShellUpload extends CakeTestModel
{
    public $useTable = 'uploads';

    public $actsAs = [
        'Upload.Upload' => [
            'photo' => [
                'path' => '{ROOT}tmp{DS}tests{DS}thumbnail_shell{DS}{field}{DS}',
                'thumbnailMethod' => 'php',
                'thumbnailSizes' => [
                    'thumb' => '20w',
                ],
                'deleteOnUpdate' => true,
            ],
        ],
    ];
}

class ThumbnailShellFileImport extends CakeTestModel
{
    public $useTable = 'uploads';

    public $actsAs = [
        'Upload.FileImport' => [
            'photo' => [
                'path' => '{ROOT}tmp{DS}tests{DS}thumbnail_shell{DS}{field}{DS}',
                'thumbnailMethod' => 'php',
                'thumbnailSizes' => [
                    'small' => '10w',
                ],
            ],
        ],
    ];
}

class ThumbnailShellNoUpload extends CakeTestModel
{
    public $useTable = 'uploads';
}

class ThumbnailShellTest extends CakeTestCase
{
    public $fixtures = ['plugin.upload.upload'];

    /**
     * @var ThumbnailShell|\PHPUnit\Framework\MockObject\MockObject
     */
    public $Shell;

    /**
     * @var string
     */
    protected $_path;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->_path = TMP . 'tests' . DS . 'thumbnail_shell' . DS . 'photo' . DS;

        $out = $this->getMockBuilder(ConsoleOutput::class)->disableOriginalConstructor()->getMock();
        $in = $this->getMockBuilder(ConsoleInput::class)->disableOriginalConstructor()->getMock();
        $this->Shell = $this->getMockBuilder(ThumbnailShell::class)
            ->setConstructorArgs([$out, $out, $in])
            ->onlyMethods(['in', 'out', 'err', '_stop'])
            ->getMock();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $folder = new Folder();
        $folder->delete(TMP . 'tests' . DS . 'thumbnail_shell');
        unset($this->Shell);
        ClassRegistry::flush();

        parent::tearDown();
    }

    /**
     * Creates the source image of the fixture record #1
     *
     * @return string
     */
    protected function _createSourceImage(): string
    {
        $dir = $this->_path . '1' . DS;
        mkdir($dir, 0777, true);
        $image = imagecreatetruecolor(100, 50);
        imagepng($image, $dir . 'Photo.png');

        return $dir . 'Photo.png';
    }

    public function testGenerate(): void
    {
        $source = $this->_createSourceImage();

        $this->Shell->expects($this->once())->method('in')->willReturn('ThumbnailShellUpload');
        $this->Shell->expects($this->never())->method('_stop');
        $this->Shell->expects($this->once())
            ->method('out')
            ->with('Created thumbnails in 1 for Photo.png');

        $this->Shell->generate();

        $this->assertFileExists($source);
        $thumbnail = $this->_path . '1' . DS . 'thumb_Photo.png';
        $this->assertFileExists($thumbnail);
        $this->assertSame([20, 10], array_slice(getimagesize($thumbnail), 0, 2));

        $Model = ClassRegistry::getObject('ThumbnailShellUpload');
        $record = $Model->findById(1);
        $this->assertSame('Photo.png', $record['ThumbnailShellUpload']['photo']);
        $this->assertEquals(1, $record['ThumbnailShellUpload']['dir']);
        $this->assertSame('image/png', $record['ThumbnailShellUpload']['type']);
        $this->assertTrue($Model->Behaviors->Upload->settings['ThumbnailShellUpload']['photo']['deleteOnUpdate']);
    }

    public function testGenerateWithFileImportBehavior(): void
    {
        $this->_createSourceImage();

        $this->Shell->expects($this->once())->method('in')->willReturn('ThumbnailShellFileImport');
        $this->Shell->expects($this->never())->method('_stop');

        $this->Shell->generate();

        $this->assertFileExists($this->_path . '1' . DS . 'small_Photo.png');
    }

    public function testGenerateWithoutUploadBehavior(): void
    {
        $this->Shell->expects($this->once())->method('in')->willReturn('ThumbnailShellNoUpload');
        $this->Shell->expects($this->once())
            ->method('err')
            ->with($this->stringContains("Model 'ThumbnailShellNoUpload' does not use the Upload behavior."));
        $this->Shell->expects($this->once())->method('_stop');

        $this->Shell->generate();
    }

    public function testGenerateWithMissingModel(): void
    {
        $this->Shell->expects($this->once())->method('in')->willReturn('ThumbnailShellMissingModel');
        $this->Shell->expects($this->once())
            ->method('err')
            ->with($this->stringContains("Model 'ThumbnailShellMissingModel' not found."));
        $this->Shell->expects($this->once())->method('_stop');

        $this->Shell->generate();
    }
}
