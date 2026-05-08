<?php

/**
 * Centralized vendor asset paths.
 *
 * All third-party library files are stored locally under public/vendor/.
 * Reference these in Blade templates via:  {{ asset(config('assets.KEY')) }}
 *
 * To upgrade a library:
 *  1. Replace the file under public/vendor/
 *  2. Update the path here if the filename changes.
 *  3. No Blade templates need to change.
 */

return [

    // ── JavaScript ────────────────────────────────────────────────────────────

    'jquery'         => 'vendor/jquery/jquery-3.7.1.min.js',
    'datatables_js'  => 'vendor/datatables/jquery.dataTables.min.js',
    'sweetalert2_js' => 'vendor/sweetalert2/sweetalert2.all.min.js',
    'chartjs'        => 'vendor/chartjs/chart.umd.min.js',

    // ── CSS ───────────────────────────────────────────────────────────────────

    'font_awesome'       => 'vendor/fontawesome/css/all.min.css',

    'datatables_css'     => 'vendor/datatables/jquery.dataTables.min.css',
    'datatables_bs5_css' => 'vendor/datatables/dataTables.bootstrap5.min.css',
    'sweetalert2_css'    => 'vendor/sweetalert2/sweetalert2.min.css',

    // ── Fonts (self-hosted, served from public/vendor/fonts/) ────────────────

    'font_dm_sans'        => 'vendor/fonts/dm-sans/dm-sans.css',
    'font_inter'          => 'vendor/fonts/inter/inter.css',
    'font_material_icons' => 'vendor/fonts/material-icons/material-icons.css',

];
