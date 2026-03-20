<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade steps for block_servermon.
 *
 * @package   block_servermon
 * @copyright 2026 Vernon Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the block_servermon plugin.
 *
 * @param int $oldversion The old plugin version.
 * @return bool
 */
function xmldb_block_servermon_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026032000) {
        // Create the metric log table if it does not already exist (fresh installs
        // use install.xml; this step covers sites upgrading from an older version).
        $table = new xmldb_table('block_servermon_log');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id',          XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10',  null, XMLDB_NOTNULL, null);
            $table->add_field('cpu_pct',     XMLDB_TYPE_NUMBER,  '6,2', null, null,          null);
            $table->add_field('cpu_load1',   XMLDB_TYPE_NUMBER,  '8,4', null, null,          null);
            $table->add_field('ram_pct',     XMLDB_TYPE_NUMBER,  '6,2', null, null,          null);
            $table->add_field('ram_used',    XMLDB_TYPE_NUMBER,  '10,4',null, null,          null);
            $table->add_field('ram_total',   XMLDB_TYPE_NUMBER,  '10,4',null, null,          null);
            $table->add_field('disk_pct',    XMLDB_TYPE_NUMBER,  '6,2', null, null,          null);
            $table->add_field('disk_used',   XMLDB_TYPE_NUMBER,  '10,4',null, null,          null);
            $table->add_field('disk_total',  XMLDB_TYPE_NUMBER,  '10,4',null, null,          null);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

            $dbman->create_table($table);
        }

        upgrade_block_savepoint(true, 2026032000, 'servermon');
    }

    return true;
}
