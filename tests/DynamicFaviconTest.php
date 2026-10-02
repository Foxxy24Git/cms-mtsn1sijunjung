<?php

use PHPUnit\Framework\TestCase;

final class DynamicFaviconTest extends TestCase
{
    public function testPublicLayoutUsesUploadedSchoolLogoAsFavicon(): void
    {
        $layout = file_get_contents(APPPATH . 'Views/layouts/header.php');

        $this->assertStringContainsString('<link rel="icon" href="<?= esc($school_logo) ?>">', $layout);
    }
}
