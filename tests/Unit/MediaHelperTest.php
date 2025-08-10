<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helper\MediaHelper;
use PHPUnit\Framework\TestCase;

class MediaHelperTest extends TestCase
{
    public function test_can_get_upload_limits()
    {
        $limits = MediaHelper::getUploadLimits();

        $this->assertIsArray($limits);
        $this->assertArrayHasKey('upload_max_filesize', $limits);
        $this->assertArrayHasKey('post_max_size', $limits);
        $this->assertArrayHasKey('effective_max_filesize', $limits);
        $this->assertArrayHasKey('max_file_uploads', $limits);

        // Check that effective limit is correct
        $expected = min($limits['upload_max_filesize'], $limits['post_max_size']);
        $this->assertEquals($expected, $limits['effective_max_filesize']);
    }

    public function test_can_parse_php_size_strings()
    {
        $this->assertEquals(1024, MediaHelper::parseSize('1K'));
        $this->assertEquals(1024 * 1024, MediaHelper::parseSize('1M'));
        $this->assertEquals(1024 * 1024 * 1024, MediaHelper::parseSize('1G'));
        $this->assertEquals(2048, MediaHelper::parseSize('2K'));
        $this->assertEquals(10 * 1024 * 1024, MediaHelper::parseSize('10M'));
    }

    public function test_format_file_size()
    {
        $this->assertEquals('1 KB', MediaHelper::formatFileSize(1024));
        $this->assertEquals('1 MB', MediaHelper::formatFileSize(1024 * 1024));
        $this->assertEquals('1 GB', MediaHelper::formatFileSize(1024 * 1024 * 1024));
        $this->assertEquals('500 B', MediaHelper::formatFileSize(500));
    }
}
