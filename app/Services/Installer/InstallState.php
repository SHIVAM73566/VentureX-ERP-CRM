<?php

declare(strict_types=1);

namespace App\Services\Installer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Singleton installation state stored in the database.
 *
 * The storage/.installed flag is kept for backwards compatibility, but the
 * database row is authoritative for the chosen install mode and lets the
 * installer guard lock the wizard even when the file was removed by mistake.
 *
 * @author Shivam Chaturvedi <chaturvedishivam179@gmail.com>
 */
class InstallState
{
    public const TABLE = 'install_state';

    public const SINGLETON_ID = 1;

    public function tableExists(): bool
    {
        try {
            return Schema::hasTable(self::TABLE);
        } catch (Throwable) {
            return false;
        }
    }

    public function isInstalled(): bool
    {
        if (! $this->tableExists()) {
            return false;
        }

        try {
            return (bool) DB::table(self::TABLE)
                ->where('id', self::SINGLETON_ID)
                ->value('installed');
        } catch (Throwable) {
            return false;
        }
    }

    public function mode(): ?string
    {
        if (! $this->tableExists()) {
            return null;
        }

        try {
            $mode = DB::table(self::TABLE)
                ->where('id', self::SINGLETON_ID)
                ->value('mode');

            return is_string($mode) && $mode !== '' ? $mode : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Persist the singleton row. Only known columns are written so a partially
     * migrated database cannot break the installer.
     */
    public function record(?string $mode = null, ?bool $installed = null, array $meta = []): void
    {
        if (! $this->tableExists()) {
            return;
        }

        $values = [];
        if ($mode !== null) {
            $values['mode'] = $mode;
        }
        if ($installed !== null) {
            $values['installed'] = $installed;
            $values['installed_at'] = $installed ? now() : null;
        }
        if ($meta !== []) {
            $values['meta'] = json_encode($meta);
        }

        if ($values === []) {
            return;
        }

        DB::table(self::TABLE)->updateOrInsert(
            ['id' => self::SINGLETON_ID],
            array_merge($values, ['updated_at' => now()])
        );
    }

    /**
     * Test/support helper: return the installer to its pristine state.
     */
    public function reset(): void
    {
        if (! $this->tableExists()) {
            return;
        }

        DB::table(self::TABLE)->updateOrInsert(
            ['id' => self::SINGLETON_ID],
            [
                'installed' => false,
                'mode' => null,
                'installed_at' => null,
                'meta' => null,
                'updated_at' => now(),
            ]
        );
    }
}
