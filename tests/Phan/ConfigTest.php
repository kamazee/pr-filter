<?php

namespace Kamazee\PhpqaReportTool\Phan;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function sprintf;

class ConfigTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $vfs;

    public function setUp()
    {
        $this->vfs = vfsStream::setup();
    }

    public function testThrowsWhenCantReadConfig()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionCode(ConfigException::CANT_READ_CONFIG_CODE);

        $file = "{$this->vfs->url()}/i_do_not_exist";
        $this->expectExceptionMessageRegExp(
            sprintf('#Failed to read.*%s.*#', $file)
        );

        new Config($file);
    }

    /**
     * @dataProvider configNotAnArrayDataProvider
     *
     * @param string $brokenConfig
     */
    public function testThrowsWhenConfigIsNotAnArray(string $brokenConfig)
    {
        $file = vfsStream::newFile('config.php')
            ->at($this->vfs)
            ->setContent($brokenConfig)
            ->url();

        $this->expectExceptionObject(
            ConfigException::configIsNotAnArray($file)
        );

        new Config($file);
    }

    public static function configNotAnArrayDataProvider()
    {
        return [
            ['<?php return 42;'],
            ['<?php'],
            ['<?php return null;'],
            ['<?php return "test";'],
        ];
    }

    /**
     * @throws ConfigException
     */
    public function testThrowsWhenWritingFails()
    {
        $file = vfsStream::newFile('config.php')
            ->at($this->vfs)
            ->setContent('<?php return [];')
            ->chmod(0400)
            ->url();

        $this->expectException(ConfigException::class);
        $this->expectExceptionCode(ConfigException::WRITING_CONFIG_FAILED_CODE);
        $this->expectExceptionMessageRegExp(
            sprintf('#Failed to write.*%s.*file_put_contents.*#', $file)
        );

        (new Config($file))->write($file);
    }

    /**
     * @throws ConfigException
     */
    public function testWritesFromDistToOtherFile()
    {
        $initialContent =
            '<?php return ["test" => true]; /* WILL DROP IT ON var_export */';

        $input = vfsStream::newFile('config.php')
            ->at($this->vfs)
            ->setContent($initialContent)
            ->url();

        $output = "{$this->vfs->url()}/new_config.php";

        $this->assertFileNotExists($output);
        (new Config($input))->write($output);
        $this->assertFileExists($output);

        // result of dumping variable will differ...
        $this->assertFileNotEquals(
            $input,
            $output
        );

        // ... but the array itself will be the same
        $this->assertEquals(
            require $input,
            require $output
        );
    }

    /**
     * @throws ConfigException
     */
    public function testOverridesDist()
    {
        $initialContent =
            '<?php return ["test" => true]; /* WILL DROP IT ON var_export */';

        $input = vfsStream::newFile('config.php')
            ->at($this->vfs)
            ->setContent($initialContent)
            ->url();

        (new Config($input))->write($input);

        $this->assertNotEquals(
            $initialContent,
            file_get_contents($input)
        );

        $this->assertEquals(
            // trim '<?php '
            eval(substr($initialContent, 6)),
            require $input
        );
    }

    /**
     * @throws ConfigException
     */
    public function testWritesWithoutDist()
    {
        $output = "{$this->vfs->url()}/new_config.php";
        (new Config())->write($output);

        $this->assertEquals(
            [],
            require $output
        );
    }

    public function testAddsAnalyzedFiles()
    {
        $changedFiles = ['test1.php', 'test2.php'];
        $expectedConfig = [
            'file_list' => $changedFiles,
        ];

        $input = vfsStream::newFile('config.php')
            ->at($this->vfs)
            ->setContent('<?php return [];')
            ->url();


        $config = new Config($input);
        $config->setAnalyzedFiles($changedFiles);
        $config->write($input);

        $this->assertEquals(
            $expectedConfig,
            require $input
        );
    }
}
