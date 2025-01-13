<?php
/**
 * CP Helper for Double Secret Agency
 *
 * Internal tools for the Craft CMS control panel.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2023 Double Secret Agency
 */

namespace doublesecretagency\cp\console;

use Composer\Script\Event;

/**
 * Composer scripts
 *
 *   "scripts": {
 *     "import-db": "doublesecretagency\\cp\\console\\ComposerScripts::importDb"
 *   }
 *
 * @since 2.2.0
 */
class ComposerScripts
{

    /**
     * Import database from backup.
     *
     * @param Event $event
     */
    public static function importDb(Event $event): void
    {
        // Define color codes
        $colorRed = "\033[31m";
        $colorGreen = "\033[32m";
        $colorYellow = "\033[33m";
        $colorReset = "\033[0m";

        // Get current database dump
        $files = glob(getcwd().'/db/*');
        $sql = ($files ? $files[0] : false);
        $filename = ($sql ? basename($sql) : false);

        // If no SQL file exists, exit immediately
        if (!$filename) {
            echo "{$colorRed}🚫 Unable to import, no backup file was found in the `db` directory.{$colorReset}\n";
            exit();
        }

        // Get full path to SQL file
        $filepath = getcwd()."/db/{$filename}";

        // Import the SQL file
        echo "\nIMPORTING: {$colorYellow}/db/{$filename}{$colorReset}\n";
        system("mysql -u db -pdb db < {$filepath}");
        echo "{$colorGreen}Database import complete. 👍{$colorReset}\n\n";

        // Clear all caches
        system("php craft clear-caches/all --color=1");
    }

}
