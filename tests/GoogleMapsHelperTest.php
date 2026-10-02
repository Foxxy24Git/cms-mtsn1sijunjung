<?php

use PHPUnit\Framework\TestCase;

final class GoogleMapsHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('maps');
    }

    public function testBuildsEmbedUrlFromValidCoordinates(): void
    {
        $this->assertTrue(function_exists('google_maps_embed_url'));
        $this->assertSame(
            'https://www.google.com/maps?q=-0.688211,100.953739&z=16&output=embed',
            google_maps_embed_url('-0.688211', '100.953739')
        );
    }

    public function testRejectsInvalidCoordinates(): void
    {
        $this->assertTrue(function_exists('google_maps_embed_url'));
        $this->assertNull(google_maps_embed_url('-91', '100'));
        $this->assertNull(google_maps_embed_url('-0.68', '181'));
        $this->assertNull(google_maps_embed_url('invalid', '100'));
    }
}
