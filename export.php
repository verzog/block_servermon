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
 * CSV export of the block_servermon metric log.
 *
 * Accessible to site administrators only. Streams up to the most recent
 * 10,000 rows as a CSV file.
 *
 * @package   block_servermon
 * @copyright 2026 Vernon Spain
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

if (!is_siteadmin()) {
    throw new \moodle_exception('accessdenied', 'admin');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/servermon/export.php'));

// Stream the CSV directly — no need to buffer the whole result set in memory.
$filename = 'servermon_log_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM so Excel opens the file with the correct encoding.
fputs($out, "\xEF\xBB\xBF");

fputcsv($out, [
    'Timestamp',
    'CPU % (capped 100)',
    'CPU Load 1m',
    'RAM %',
    'RAM Used (GB)',
    'RAM Total (GB)',
    'Disk %',
    'Disk Used (GB)',
    'Disk Total (GB)',
]);

$records = $DB->get_recordset('block_servermon_log', null, 'timecreated DESC',
    'id,timecreated,cpu_pct,cpu_load1,ram_pct,ram_used,ram_total,disk_pct,disk_used,disk_total',
    0, 10000);

foreach ($records as $r) {
    fputcsv($out, [
        date('Y-m-d H:i:s', (int)$r->timecreated),
        $r->cpu_pct   !== null ? number_format((float)$r->cpu_pct,  2, '.', '') : '',
        $r->cpu_load1 !== null ? number_format((float)$r->cpu_load1, 4, '.', '') : '',
        $r->ram_pct   !== null ? number_format((float)$r->ram_pct,  2, '.', '') : '',
        $r->ram_used  !== null ? number_format((float)$r->ram_used,  4, '.', '') : '',
        $r->ram_total !== null ? number_format((float)$r->ram_total, 4, '.', '') : '',
        $r->disk_pct  !== null ? number_format((float)$r->disk_pct, 2, '.', '') : '',
        $r->disk_used !== null ? number_format((float)$r->disk_used, 4, '.', '') : '',
        $r->disk_total!== null ? number_format((float)$r->disk_total,4, '.', '') : '',
    ]);
}

$records->close();
fclose($out);
exit;
