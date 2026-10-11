<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Evita requerir public/build/manifest.json en tests (sin npm run build)
        \Illuminate\Support\Facades\Vite::useBuildDirectory('build');
        if (! file_exists(public_path('build/manifest.json'))) {
            $this->withoutVite();
        }
    }
}
