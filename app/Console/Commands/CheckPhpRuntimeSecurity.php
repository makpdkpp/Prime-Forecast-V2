<?php

namespace App\Console\Commands;

use App\Support\PhpRuntimeSecurity;
use Illuminate\Console\Command;

class CheckPhpRuntimeSecurity extends Command
{
    protected $signature = 'security:check-php-runtime';

    protected $description = 'Check this CLI runtime against the PHP2Shell deployment requirements';

    public function handle(): int
    {
        $this->line('PHP '.PHP_VERSION.' ('.PHP_SAPI.')');
        $this->line('Loaded php.ini: '.(php_ini_loaded_file() ?: '(none)'));
        $this->line('Additional ini files: '.(php_ini_scanned_files() ?: '(none)'));
        $this->warn('CLI results do not verify the web runtime or that existing web workers were restarted.');

        $valid = true;

        if (!PhpRuntimeSecurity::isPatchedVersion(PHP_VERSION)) {
            $this->error('Requires a stable patched PHP branch: 8.2.35+, 8.3.36+, 8.4.27+ or 8.5.12+.');
            $valid = false;
        }

        // A missing session extension is also an unexpected configuration for this app.
        if (!extension_loaded('session') || filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            $this->error('Load the session extension and set session.upload_progress.enabled=0 in server-level PHP configuration.');
            $valid = false;
        }

        if (!$valid) {
            $this->line('See deploy/PHP2SHELL.md.');

            return self::FAILURE;
        }

        $this->info('This CLI runtime meets the checked PHP2Shell version and upload-progress requirements.');

        return self::SUCCESS;
    }
}
