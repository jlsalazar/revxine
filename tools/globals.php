<?php
/*
 * globals.php REVXINE system, globals of REVXINE Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
$boundary = "--12544829573152";
$CBSstatus_patron = "/<title>Job status of [A-Z0-9]*(.*)<\/title>/";
$CBSresult_patron = "/<title>(.*)results(.*)<\/title>/";

$ERRORS = array (
    0 => 'is ok',
    1 => 'Error: El servidor no pudo resolver la peticion',
    2 => 'Error: El servidor fue interrumpido',
    3 => 'Error: Imposible acceder al servidor',
    4 => 'Error: El filtrado de CELLO fue excesivo.' 
);

?>
