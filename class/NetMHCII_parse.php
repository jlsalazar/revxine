<?php
/*
 * NetMHCII_parse.php REVXINE system, parse class of NetMHCII Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>
<?php

include_once ('tools/tools.php');
include_once ('class/Fasta.php');
class NetMHCII_parse {
  public $attr = array (
      'peptide_len',
      'thrs',
      'thas',
      'thrw',
      'thaw' 
  );
  public $results = array ();
  public $original;
  function __construct($Result) {
    $this->original = str_replace ( "<=", "", $Result );
    $this->getAttr ();
    
    $results = divideResults ( $this->original, '# Allele:' );
    foreach ( $results as $key => $result ) {
      $this->results [$key] = array (
          'result',
          'allele',
          'lines',
          'peptidesTable',
          'protein',
          'strongBinders',
          'weakBinders' 
      );
      $this->results [$key] ['result'] = $result;
      $this->results [$key] ['allele'] = $this->getAllele ( $result );
      $this->results [$key] ['lines'] = $this->getLines ( $this->results [$key] ['result'] );
      $this->results [$key] ['peptidesTable'] = $this->getTable ( $this->results [$key] ['lines'] );
      $this->results [$key] ['protein'] = $this->getProtein ( $this->results [$key] ['peptidesTable'] [1] );
      $this->results [$key] ['strongBinders'] = $this->getSBinders ( $this->results [$key] ['peptidesTable'] [1] );
      $this->results [$key] ['weakBinders'] = $this->getWBinders ( $this->results [$key] ['peptidesTable'] [1] );
    }
  }
  function getSBinders($table) {
    $sb = array ();
    foreach ( $table as $key => $value ) {
      $len = count ( $value );
      if ($value [$len - 1] == 'SB')
        $sb [$key] = $value;
    }
    $sb = array_values ( $sb );
    return $sb;
  }
  function getWBinders($table) {
    $wb = array ();
    foreach ( $table as $key => $value ) {
      $len = count ( $value );
      if ($value [$len - 1] == 'WB')
        $wb [$key] = $value;
    }
    $wb = array_values ( $wb );
    return $wb;
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
      unset ( $content [$key] [0] );
      $content [$key] = array_values ( $content [$key] );
    }
    
    $content = updateIdentityMHCII ( $content );
    return array (
        $header,
        $content 
    );
  }
  function getProtein($string) {
    return $string [0] [3];
  }
  function getLines($result) {
    $lines = array ();
    $lines = explode ( "-------------------------------------------------------------------------------------------------------------------------------------------", $result );
    unset ( $lines [0] );
    unset ( $lines [4] );
    $lines = array_values ( $lines );
    return $lines;
  }
  function getAllele($result) {
    $pos2 = strpos ( $result, "\n", 0 );
    $pos = 9;
    $allele = str_replace ( array (
        " ",
        "\n",
        "\r" 
    ), "", substr ( $result, $pos, $pos2 - $pos ) );
    return $allele;
  }
  function getAttr() {
    $pos = strpos ( $this->original, '# Peptide length ', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 17;
    $this->attr ['peptide_len'] = substr ( $this->original, $pos, $pos2 - $pos );
    
    $pos = strpos ( $this->original, '# Threshold for Strong binding peptides (IC50)', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 47;
    $this->attr ['thas'] = str_replace ( array (
        " ",
        "nM" 
    ), "", substr ( $this->original, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $this->original, '# Threshold for Weak binding peptides (IC50)', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 45;
    $this->attr ['thaw'] = str_replace ( array (
        " ",
        "nM" 
    ), "", substr ( $this->original, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $this->original, '# Threshold for Strong binding peptides (%Rank)', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 48;
    $this->attr ['thrs'] = str_replace ( " ", "", substr ( $this->original, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $this->original, '# Threshold for Weak binding peptides (%Rank)', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 46;
    $this->attr ['thrw'] = str_replace ( " ", "", substr ( $this->original, $pos, $pos2 - $pos ) );
  }
  function printResults() {
    print_r ( $this->results );
  }
  function filter_bindings() {
    $filter = '';
    
    $filter .= "<b>Longitud del peptido:  </b>" . $this->attr ['peptide_len'] . "<br><br>";
    $filter .= "<b>Umbral de uni&oacuten fuerte (IC50):  </b>" . $this->attr ['thas'] . "<br>";
    $filter .= "<b>Umbral de uni&oacuten d&eacutebil (IC50):  </b>" . $this->attr ['thaw'] . "<br>";
    $filter .= "<b>Umbral de uni&oacuten fuerte (%Rank):  </b>" . $this->attr ['thrs'] . "<br>";
    $filter .= "<b>Umbral de uni&oacuten d&eacutebil (%Rank):  </b>" . $this->attr ['thrw'] . "<br>";
    $filter .= '<br>';
    
    foreach ( $this->results as $key => $result ) {
      $filter .= "<div> <h3>" . $result ['allele'] . " | " . $result ['protein'];
      $filter .= "</h3></div>";
      
      $filter .= "<table style='width:50%;'>";
      $filter .= "<tr>
										<td id='mhc_header'>Peptidos</td>
										<td id='mhc_header'>Uniones fuertes</td>
										<td id='mhc_header'>Uniones debiles</td>
									</tr>";
      $filter .= "<tr>";
      $filter .= "<td>" . count ( $result ['peptidesTable'] [1] ) . "</td>";
      $filter .= "<td>" . count ( $result ['strongBinders'] ) . "</td>";
      $filter .= "<td>" . count ( $result ['weakBinders'] ) . "</td>";
      $filter .= "</tr>";
      $filter .= "</table>";
      
      $filter .= "<table style='width:100%;'>";
      $filter .= "<tr>";
      foreach ( $result ['peptidesTable'] [0] as $key2 => $result2 )
        $filter .= "<td id='mhc_header'>$result2</td>";
      $filter .= "</tr>";
      
      foreach ( $result ['strongBinders'] as $key2 => $result2 ) {
        $filter .= "<tr>";
        foreach ( $result2 as $key3 => $result3 ) {
          if ($key3 == 2) {
            $remplace = "<span class='bluetagS'>" . $result2 [5] . "</span>";
            $result3 = str_replace ( $result2 [5], $remplace, $result3 );
          }
          if ($key3 == 5)
            $result3 = "<span class='bluetagS'>" . $result3 . "</span>";
          $filter .= "<td>$result3</td>";
        }
        $filter .= "</tr>";
      }
      
      foreach ( $result ['weakBinders'] as $key2 => $result2 ) {
        $filter .= "<tr>";
        foreach ( $result2 as $key3 => $result3 ) {
          if ($key3 == 2) {
            $remplace = "<span class='bluetagW'>" . $result2 [5] . "</span>";
            $result3 = str_replace ( $result2 [5], $remplace, $result3 );
          }
          if ($key3 == 5)
            $result3 = "<span class='bluetagW'>" . $result3 . "</span>";
          $filter .= "<td>$result3</td>";
        }
        $filter .= "</tr>";
      }
      
      $filter .= "</table>";
    }
    
    savefile("parseMHCII",print_r($this->results,true));
    return $filter;
  }
}
?>
			
