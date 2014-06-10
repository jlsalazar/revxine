<?php
/*
 * results.php REVXINE system, results page of REVXINE Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/curl_tools.php');
include_once ('tools/tools.php');
include_once ('tools/globals.php');
include_once ('tools/PrintResults.php');
include_once ('class/classes.php');
?>

<?php
$sequence = $_POST ['SEQPASTE'];
if (isset ( $_FILES ['SEQSUB'] ) && ! empty ( $_FILES ['SEQSUB'] ['tmp_name'] ))
  $sequence = file_get_contents ( $_FILES ['SEQSUB'] ['tmp_name'], true );

if (FASTA_validate ( $sequence )) {
  $fasta = new Fasta ( $sequence );
  
  $aux = array ();
  $aux ['Cello'] = new Cello (); // always be the first on array
  $aux ['NetMHCI'] = new NetMHCI ();
  $aux ['NetMHCII'] = new NetMHCII ();
  $aux ['BepiPred'] = new BepiPred ();
  $servers = array ();
  
  foreach ( $aux as $server => $object ) {
    if (isset ( $_POST [$server] )) {
      if ($fasta->fasta_string () != '') {
        $object->generatePost ( $fasta->fasta_string (), $_POST, $_FILES, $boundary );
        $http = curl_sendPost ( $object->getUrl (), $object->getPost (), $boundary );
        $object->verificateResponse ( $http );
      } else
        $object->setResult_isOK ( 4 );
        
      
      if ($object->getResult_isOK () == 0) {
        $object->AllocateParams ( $_POST );
        $object->generateStruct ();
        if ($server == 'Cello')
          $fasta = $object->UpdateFasta ( $fasta );
        $object->generateFilters ( $fasta );
      }
      
      $servers [$server] = $object;
    }
  }
  
  $servers ['Reportes'] = new Reports ( $servers, $fasta );
  
  printResults ( $servers, $ERRORS );
} else
  echo "La secuencia no se encuentra en correcto formato FASTA.";
?>
