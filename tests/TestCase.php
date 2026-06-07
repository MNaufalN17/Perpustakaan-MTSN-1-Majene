<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledViewPath = storage_path('framework/testing/views');

        if (! is_dir($compiledViewPath)) {
            mkdir($compiledViewPath, 0777, true);
        }

        config([
            'view.compiled' => $compiledViewPath,
        ]);
    }
}