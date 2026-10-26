<?php

namespace App\Support;

final class PhpRuntimeSecurity
{
    /** Minimum upstream releases fixing CVE-2026-109618 and CVE-2026-109619. */
    public static function isPatchedVersion(string $version): bool
    {
        $minimums = [
            '8.2' => '8.2.35',
            '8.3' => '8.3.36',
            '8.4' => '8.4.27',
            '8.5' => '8.5.12',
        ];

        // Fail closed for prereleases and branches not yet reviewed here.
        if (!preg_match('/^(8\.[2-5])\.\d+(?:-\d[A-Za-z0-9.+~]*)?$/D', $version, $matches)) {
            return false;
        }

        return version_compare($version, $minimums[$matches[1]], '>=');
    }
}
