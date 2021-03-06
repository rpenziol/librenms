<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2017 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.17163.1.1.1.1.0', '-OQv'), '"');
$serial   = trim(snmp_get($device, '.1.3.6.1.4.1.17163.1.1.1.2.0', '-OQv'), '"');
$version  = trim(snmp_get($device, '.1.3.6.1.4.1.17163.1.1.1.3.0', '-OQv'), '"');


/* optimisation oids
 *
 * half-open   .1.3.6.1.4.1.17163.1.1.5.2.3.0
 * half-closed .1.3.6.1.4.1.17163.1.1.5.2.4.0
 * establised  .1.3.6.1.4.1.17163.1.1.5.2.5.0
 * active      .1.3.6.1.4.1.17163.1.1.5.2.6.0
 * total       .1.3.6.1.4.1.17163.1.1.5.2.7.0
 *
 */

$conn_array = array(
    '.1.3.6.1.4.1.17163.1.1.5.2.3.0',
    '.1.3.6.1.4.1.17163.1.1.5.2.4.0',
    '.1.3.6.1.4.1.17163.1.1.5.2.5.0',
    '.1.3.6.1.4.1.17163.1.1.5.2.6.0',
    '.1.3.6.1.4.1.17163.1.1.5.2.7.0',
);
$connections = snmp_get_multi_oid($device, $conn_array);

$conn_half_open   = $connections['.1.3.6.1.4.1.17163.1.1.5.2.3.0'];
$conn_half_closed = $connections['.1.3.6.1.4.1.17163.1.1.5.2.4.0'];
$conn_established = $connections['.1.3.6.1.4.1.17163.1.1.5.2.5.0'];
$conn_active      = $connections['.1.3.6.1.4.1.17163.1.1.5.2.6.0'];
$conn_total       = $connections['.1.3.6.1.4.1.17163.1.1.5.2.7.0'];

if ($conn_half_open >= 0 && $conn_half_closed >= 0 && $conn_established >= 0 && $conn_active >= 0 && $conn_total >= 0) {
    $rrd_def = array(
        'DS:half_open:GAUGE:600:0:U',
        'DS:half_closed:GAUGE:600:0:U',
        'DS:established:GAUGE:600:0:U',
        'DS:active:GAUGE:600:0:U',
        'DS:total:GAUGE:600:0:U',
    );

    $fields = array(
        'half_open'   => $conn_half_open,
        'half_closed' => $conn_half_closed,
        'established' => $conn_established,
        'active'      => $conn_active,
        'total'       => $conn_total,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_connections', $tags, $fields);
    $graphs['riverbed_connections'] = true;
}

/* datastore oids
 *
 * hits .1.3.6.1.4.1.17163.1.1.5.4.1.0
 * miss .1.3.6.1.4.1.17163.1.1.5.4.2.0
 *
 */
$datastore_array = array(
    '.1.3.6.1.4.1.17163.1.1.5.4.1.0',
    '.1.3.6.1.4.1.17163.1.1.5.4.2.0',
);
$datastore = snmp_get_multi_oid($device, $datastore_array);

$datastore_hits = $datastore['.1.3.6.1.4.1.17163.1.1.5.4.1.0'];
$datastore_miss = $datastore['.1.3.6.1.4.1.17163.1.1.5.4.2.0'];

if ($datastore_hits >= 0 && $datastore_miss >= 0) {
    $rrd_def = array(
        'DS:datastore_hits:GAUGE:600:0:U',
        'DS:datastore_miss:GAUGE:600:0:U',
    );

    $fields = array(
        'datastore_hits' => $datastore_hits,
        'datastore_miss' => $datastore_miss,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_datastore', $tags, $fields);
    $graphs['riverbed_datastore'] = true;
}

/* optimization oids
 *
 * optimized   .1.3.6.1.4.1.17163.1.1.5.2.1.0
 * passthrough .1.3.6.1.4.1.17163.1.1.5.2.2.0
 *
 */
$optimization_array = array(
    '.1.3.6.1.4.1.17163.1.1.5.2.1.0',
    '.1.3.6.1.4.1.17163.1.1.5.2.2.0',
);

$optimizations = snmp_get_multi_oid($device, $optimization_array);

$conn_optimized   = $optimizations['.1.3.6.1.4.1.17163.1.1.5.2.1.0'];
$conn_passthrough = $optimizations['.1.3.6.1.4.1.17163.1.1.5.2.2.0'];

if ($conn_optimized >= 0 && $conn_passthrough >= 0) {
    $rrd_def = array(
        'DS:conn_optimized:GAUGE:600:0:U',
        'DS:conn_passthrough:GAUGE:600:0:U',
    );

    $fields = array(
        'conn_optimized' => $conn_optimized,
        'conn_passthrough' => $conn_passthrough,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_optimization', $tags, $fields);
    $graphs['riverbed_optimization'] = true;
}

/* bandwidth passthrough
 *
 * in .1.3.6.1.4.1.17163.1.1.5.3.3.1.0
 * out .1.3.6.1.4.1.17163.1.1.5.3.3.2.0
 * total .1.3.6.1.4.1.17163.1.1.5.3.3.3.0
 *
 */

$bandwidth_array = array(
    '.1.3.6.1.4.1.17163.1.1.5.3.3.1.0',
    '.1.3.6.1.4.1.17163.1.1.5.3.3.2.0',
    '.1.3.6.1.4.1.17163.1.1.5.3.3.3.0',
);

$bandwidth = snmp_get_multi_oid($device, $bandwidth_array);

$bw_in = $bandwidth['.1.3.6.1.4.1.17163.1.1.5.3.3.1.0'];
$bw_out = $bandwidth['.1.3.6.1.4.1.17163.1.1.5.3.3.2.0'];
$bw_total = $bandwidth['.1.3.6.1.4.1.17163.1.1.5.3.3.3.0'];

if ($bw_in >= 0 && $bw_out >= 0 && $bw_total >= 0) {
    $rrd_def = array(
        'DS:bw_in:COUNTER:600:0:U',
        'DS:bw_out:COUNTER:600:0:U',
        'DS:bw_total:COUNTER:600:0:U',
    );

    $fields = array(
        'bw_in' => $bw_in,
        'bw_out' => $bw_out,
        'bw_total' => $bw_total,
    );

    $tags = compact('rrd_def');

    data_update($device, 'riverbed_passthrough', $tags, $fields);
    $graphs['riverbed_passthrough'] = true;
}
