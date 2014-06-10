<?php
/*
 * BepiPred_parse.php REVXINE system, parse class of BepiPred Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('class/Fasta.php');
class BepiPred_parse {
  public $results = array ();
  public $original;
  function __construct($Result) {
    $this->original = str_replace ( ". .", ".", $Result );
    
    $results = divideResults ( $this->original, '##Type Protein' );
    
    foreach ( $results as $key => $result ) {
      $this->results [$key] = array (
          'result',
          'protein',
          'seq',
          'lines',
          'table',
          'epitopes' 
      );
      $this->results [$key] ['result'] = $result;
      $this->results [$key] ['protein'] = $this->getProtein ( $result );
      $this->results [$key] ['seq'] = $this->getSeq ( $result );
      $this->results [$key] ['lines'] = $this->getLines ( $result );
      $this->results [$key] ['table'] = $this->getTable ( $this->results [$key] ['lines'] );
      $this->results [$key] ['epitopes'] = $this->getEpitopes ( $this->results [$key] ['table'] [1] );
    }
  }
  function getEpitopes($table) {
    $epitopes = array ();
    foreach ( $table as $key => $value ) {
      $len = count ( $value );
      if ($value [$len - 1] == 'E')
        $epitopes [$key] = $value;
    }
    $epitopes = array_values ( $epitopes );
    return $epitopes;
  }
  function getTable($result) {
    $header = explode ( " ", preg_replace ( "/[ ]+/", " ", $result [0] ) );
    unset ( $header [0] );
    $header = array_values ( $header );
    
    $content = explode ( "\n", $result [1] );
    unset ( $content [count ( $content ) - 1] );
    unset ( $content [0] );
    $content = array_values ( $content );
    
    foreach ( $content as $key => $value ) {
      $content [$key] = explode ( " ", preg_replace ( "/[ ]+/", " ", $value ) );
    }
    
    return array (
        $header,
        $content 
    );
  }
  function getProtein($string) {
    $pos2 = strpos ( $string, "\n", 0 );
    $pos = 14;
    
    $protein = preg_replace ( "/[ ]+/", " ", substr ( $string, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $protein, "_", 0 );
    if ($pos !== FALSE)
      $protein = substr ( $protein, 0, $pos );
    return $protein;
  }
  function getSeq($string) {
    $pos = strpos ( $string, "\n", 1 );
    $pos = strpos ( $string, "\n", $pos + 1 );
    $pos2 = strpos ( $string, "##end-Protein", 0 );
    return str_replace ( array (
        "#",
        " " 
    ), "", substr ( $string, $pos, $pos2 - $pos ) );
  }
  function getLines($result) {
    $pos = strpos ( $result, "##end-Protein", 1 );
    $pos = strpos ( $result, "\n", $pos );
    $pos2 = strpos ( $result, "#", $pos );
    $pos2 = strpos ( $result, "#", $pos2 + 1 );
    $pos2 = strpos ( $result, "#", $pos2 + 1 );
    if ($pos2 === FALSE)
      $pos2 = strlen ( $result );
    
    $string = str_replace ( "<b>", "", substr ( $result, $pos, $pos2 - $pos ) );
    $string = str_replace ( "---------------------------------------------------------------------------", "", $string );
    
    $lines = array ();
    $lines = explode ( "#", $string );
    unset ( $lines [0] );
    
    $lines = array_values ( $lines );
    return $lines;
  }
  function filter_score($score) {
    $filter = '';
    
    $filter .= "<b>Puntuaci&oacuten de umbral para la asignaci&oacuten ep&iacutetopo : </b>" . $score . "<br><br>";
    $filter .= '<br>';
    
    foreach ( $this->results as $key => $result ) {
      
      $filter .= "<div> <h3>" . $result ['protein'];
      $filter .= "</h3></div>";
      $filter .= "<div><b> Epitopos :</b> " . count ( $result ['epitopes'] );
      $filter .= "</div>";
      $filter .= "<div><b> Secuencia (" . strlen ( str_replace ( array (
          "\n",
          "\r" 
      ), "", $result ['seq'] ) ) . "aa):</b> " . $result ['seq'];
      $filter .= "</div>";
      
      $filter .= "<table style='width:70%;'>";
      $filter .= "<tr>";
      foreach ( $result ['table'] [0] as $key2 => $result2 )
        $filter .= "<td id='mhc_header'>$result2</td>";
      $filter .= "</tr>";
      
      foreach ( $result ['epitopes'] as $key2 => $result2 ) {
        $filter .= "<tr>";
        foreach ( $result2 as $key3 => $result3 )
          $filter .= "<td>$result3</td>";
        $filter .= "</tr>";
      }
      $filter .= "</table>";
    }
    savefile("parseBepiPred",print_r($this->results,true));
    return $filter;
  }
}
?>
