<?php
/*
 * Cello_parse.php REVXINE system, parse class of Cello Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('class/Fasta.php');
class Cello_parse {
  public $results = array ();
  public $original;
  function __construct($Result) {
    $original = str_replace ( array (
        "&nbsp;",
        "<br>",
        "</tr>",
        "<tr>" 
    ), "", $Result );
    $results = divideResults ( $original, '<td COLSPAN=4>SeqID:' );
    
    foreach ( $results as $key => $result ) {
      $this->results [$key] = array (
          'result',
          'seqid',
          'report',
          'prediction',
          'bests',
          'best' 
      );
      $this->results [$key] ['result'] = $result;
      $this->results [$key] ['seqid'] = $this->getSeqid ( $result );
      $this->results [$key] ['report'] = $this->getReport ( $result );
      $this->results [$key] ['prediction'] = $this->getPrediction ( $result );
      $this->results [$key] ['bests'] = $this->getBests ( $this->results [$key] ['prediction'] );
      $this->results [$key] ['best'] = $this->getBest ( $this->results [$key] ['bests'] );
    }
    $this->original = $original;
  }
  function getSeqid($result) {
    $i = strpos ( $result, '</td>', 0 );
    $seqid = substr ( $result, 20, $i - 20 );
    return $seqid;
  }
  function getReport($result) {
    $report = array (
        'Amino Acid Comp.' => array (
            'localization',
            'reliability' 
        ),
        'N-peptide Comp.' => array (
            'localization',
            'reliability' 
        ),
        'Partitioned seq. Comp.' => array (
            'localization',
            'reliability' 
        ),
        'Physico-chemical Comp.' => array (
            'localization',
            'reliability' 
        ),
        'Neighboring seq. Comp.' => array (
            'localization',
            'reliability' 
        ) 
    );
    foreach ( $report as $svm => $value ) {
      $report [$svm] ['localization'] = $this->getlocalization ( $svm, $result );
      $report [$svm] ['reliability'] = $this->getreliability ( $svm, $result );
    }
    return $report;
  }
  function getPrediction($result) {
    $i = 0;
    $prediction = array ();
    $init = strpos ( $result, '<td></td>', 0 );
    while ( $init !== FALSE ) {
      $prediction [$i] ['localization'] = $this->getlocalization ( '<td></td>', $result );
      $prediction [$i] ['reliability'] = $this->getreliability ( '<td></td>', $result );
      $init = strpos ( $result, '<td></td>', $init + 4 );
      if ($init !== FALSE) {
        $result = substr ( $result, $init, strlen ( $result ) - $init );
        $init = 0;
      }
      $i ++;
    }
    return $prediction;
  }
  function getlocalization($svm, $result) {
    $i = strpos ( $result, $svm, 0 );
    $i += 4;
    $i = strpos ( $result, '<td>', $i );
    $j = strpos ( $result, '</td>', $i );
    $i += 4;
    $seqid = trim ( substr ( $result, $i, $j - $i ) );
    return $seqid;
  }
  function getreliability($svm, $result) {
    $i = strpos ( $result, $svm, 0 );
    $i += 4;
    $i = strpos ( $result, '<td>', $i );
    $i = strpos ( $result, '</td>', $i );
    $i = strpos ( $result, '<td>', $i );
    $j = strpos ( $result, '</td>', $i );
    $i += 4;
    $seqid = substr ( $result, $i, $j - $i );
    return $seqid;
  }
  function printResults() {
    $print = '';
    print_r ( $this->results );
  }
  function getBests($prediction) {
    $bests = array ();
    $j = 0;
    foreach ( $prediction as $i => $value ) {
      if (strpos ( $value ['reliability'], '*', 0 ) !== FALSE) {
        $bests [$j] = $value;
        $bests [$j] ['reliability'] = str_replace ( '*', '', $bests [$j] ['reliability'] );
        $j ++;
      }
    }
    return $bests;
  }
  function getBest($bests) {
    $best = array ();
    if (count ( $bests ) > 0) {
      $best = $bests [0];
      foreach ( $bests as $i => $value ) {
        if ($best ['reliability'] < $value ['reliability'])
          $best = $value;
      }
    }
    
    return $best;
  }
  function filter_selection($fasta, $location, $reliability) {
    $nombre_archivo_tmp = tempnam ( "tmp/", "FTA" );
    $pos = strpos ( $nombre_archivo_tmp, 'tmp/', 0 );
    $nombre_archivo = substr ( $nombre_archivo_tmp, $pos, strlen ( $nombre_archivo_tmp ) - $pos ) . '.fasta';
    
    savefile ( $nombre_archivo, $fasta->fasta_string () );
    unlink ( $nombre_archivo_tmp );
    
    if ($reliability == 'alta')
      $cadena = '<b>Tipo de b&uacutesqueda:</b> Confiabilidad alta de localizaci&oacuten en alguna de las siguientes: ' . implode ( ",", $location );
    else
      $cadena = '<b>Tipo de b&uacutesqueda:</b> Confiabilidad normal de localizaci&oacuten en alguna de las siguientes: ' . implode ( ",", $location );
      
      // $rechazadas = $fasta->count_old - $fasta->count;
    $cadena .= '<br>';
    $cadena .= '<b>Secuencias analizadas:</b> ' . $fasta->count_old . '<br>';
    $cadena .= '<b>Secuencias aceptadas:</b> ' . $fasta->count . '<br>';
    // $cadena .= '<b>Secuencias rechazadas:</b> '.$rechazadas.'.<br>';
    $cadena .= "<i><br>Se gener&oacute un archivo fasta con las secuencias aceptadas<br> <a href=" . $nombre_archivo . " download='CelloFilter.fasta'>click aqui</a> para descargar.<br></i>";
    $cadena .= '<h3>Aceptados:</h3>';
    
    // $bests_count = count($this->results[$key]['bests']);
    
    $cadena .= "<table>";
    $cadena .= '<tr>';
    $cadena .= "<td style='text-align:center;vertical-align:middle;width:500px;background-color: rgb(177, 177, 177);'> Secuencia </td>";
    $cadena .= "<td colspan='2' style='text-align:center;vertical-align:middle;width:300px;background-color: rgb(177, 177, 177);'>  Localizaci&oacuten </td>";
    $cadena .= '</tr>';
    foreach ( $fasta->header as $key => $value ) {
      
      $cadena .= '<tr>';
      $cadena .= "<td rowspan='2' width='510px'> <div style='width:500px;height:auto;word-wrap: break-word;'>" . '>' . $fasta->header [$key] ['full_line'] . '<br>' . $fasta->sequence [$key] . '</div></td>';
      // $cadena .= '<td>'.'>'.$fasta->header[$key]['full_line'].'</td>';
      $cadena .= "<td style='text-align:center;vertical-align:middle;background-color: rgb(204, 204, 204);'> Confiabilidad alta</td>";
      $cadena .= "<td style='text-align:center;vertical-align:middle;background-color: rgb(204, 204, 204);'> Confiabilidad normal</td>";
      $cadena .= '</tr>';
      
      $cadena .= '<tr>';
      $cadena .= "<td>";
      $cadena .= $this->results [$key] ['best'] ['localization'] . ' : ' . $this->results [$key] ['best'] ['reliability'] . "<br>";
      $cadena .= '</td>';
      $cadena .= "<td>";
      foreach ( $this->results [$key] ['bests'] as $key2 => $value2 ) {
        $cadena .= $value2 ['localization'] . ' : ' . $value2 ['reliability'] . "<br>";
      }
      $cadena .= '</td>';
      $cadena .= '</tr>';
    }
    $cadena .= '</table>';
    return $cadena;
  }
}
?>
			
