<?php

namespace Tests\Unit;

use App\Support\PhpRuntimeSecurity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhpRuntimeSecurityTest extends TestCase
{
    #[DataProvider('runtimeVersions')]
    public function test_only_reviewed_patched_releases_are_accepted(string $version, bool $expected): void
    {
        $this->assertSame($expected, PhpRuntimeSecurity::isPatchedVersion($version));
    }

    public static function runtimeVersions(): array
    {
        return [
            ['8.1.34', false],
            ['8.2.34', false],
            ['8.2.35', true],
            ['8.2.36', true],
            ['8.2.35-1ubuntu1', true],
            ['8.3.30', false],
            ['8.3.35', false],
            ['8.3.36', true],
            ['8.4.26', false],
            ['8.4.27', true],
            ['8.5.11', false],
            ['8.5.12', true],
            ['8.5.12RC1', false],
            ['8.5.13-RC1', false],
            ['8.6.0', false],
            ['invalid', false],
        ];
    }
}
