<?php
/*
 * tools.php REVXINE system, tools of REVXINE Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>


<?php
function multipart_build_query($fields, $boundary) {
  $retval = '';
  foreach ( $fields as $key => $value ) {
    $retval .= "--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
  }
  $retval .= "--$boundary--";
  return $retval;
}
function savefile($filename, $txt) {
  if ($filename != '') {
    $fp = fopen ( $filename, "w+" );
    fwrite ( $fp, $txt );
    fclose ( $fp );
  }
}
function printFiles($servers) {
  foreach ( $servers as $server => $object ) {
    savefile ( $server . ".html", $object->getOriginal () );
  }
}
function printDONE($servers) {
  foreach ( $servers as $server => $object ) {
    savefile ( "TXTresults/DONE/" . $server . ".html", $object->getOriginal () );
  }
}
function FASTA_validate($fasta) {
  $isok = "/^(>[-a-zA-Z0-9]+[ -_a-zA-Z0-9\[\]\|]*\s*([A-Z*-]+\s*)+)+$/s";
  if (preg_match ( $isok, $fasta, $coincidencias, PREG_OFFSET_CAPTURE ))
    if ($coincidencias [0] [1] == 0 && strlen ( $coincidencias [0] [0] ) == strlen ( $fasta ))
      return TRUE;
  
  return FALSE;
}
function countFind($find, $string) {
  $pos = - 1;
  $i = 0;
  $pos = strpos ( $string, $find, $pos + 1 );
  while ( $pos !== FALSE ) {
    $i ++;
    $pos = strpos ( $string, $find, $pos + 1 );
  }
  return $i;
}
function divideResults($string, $head) {
  $i = 0;
  $end = (strlen ( $string ) - 1);
  $seqs = array ();
  $pos0 = strpos ( $string, $head, 0 );
  $pos1 = $pos0;
  
  if ($pos0 !== FALSE) {
    while ( $pos1 !== $end ) {
      $pos1 = strpos ( $string, $head, $pos0 + 1 );
      if ($pos1 !== FALSE) {
        $seqs [$i] = substr ( $string, $pos0, $pos1 - $pos0 );
        $pos0 = $pos1;
        $i ++;
      } else
        $pos1 = $end;
    }
    $seqs [$i] = substr ( $string, $pos0, $pos1 - $pos0 );
  }
  return $seqs;
}
function divideResultsMHC($string, $head) {
  $i = 0;
  $end = (strlen ( $string ) - 1);
  $seqs = array ();
  $pos0 = strpos ( $string, $head, 0 );
  
  if ($pos0 !== FALSE) {
    while ( $string [$pos0] != "\n" )
      $pos0 --;
    
    $pos1 = $pos0;
    
    while ( $pos1 !== $end ) {
      $pos1 = strpos ( $string, $head, $pos1 );
      if ($pos1 !== FALSE)
        $pos1 = strpos ( $string, $head, $pos1 + 1 );
      if ($pos1 !== FALSE) {
        while ( $string [$pos1] != "\n" ) {
          $pos1 --;
        }
        $seqs [$i] = substr ( $string, $pos0, $pos1 - $pos0 );
        $pos0 = $pos1;
        $i ++;
      } else
        $pos1 = $end;
    }
    $seqs [$i] = substr ( $string, $pos0, $pos1 - $pos0 );
  }
  return $seqs;
}
function updateIdentityMHCII($table) {
  foreach ( $table as $key => $result ) {
    $len = count ( $result [3] );
    $table [$key] [3] = substr ( $result [3], 0, 15 );
    $pos = strpos ( $result [3], "_", 0 );
    if ($pos !== FALSE)
      $table [$key] [3] = substr ( $result [3], 0, $pos );
  }
  return $table;
}

?>