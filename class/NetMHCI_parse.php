<?php
/*
 * NetMHCI_parse.php REVXINE system, parse class of NetMHCI Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('class/Fasta.php');
class NetMHCI_parse {
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
    $this->original = preg_replace ( "/<a(.*)a>/", "", str_replace ( "<=", "", $Result ) );
    $this->getAttr ();
    
    $results = divideResultsMHC ( $this->original, ': Estimated prediction accuracy' );
    foreach ( $results as $key => $result ) {
      $this->results [$key] = array (
          'result',
          'allele',
          'accuracy',
          'proteins' 
      );
      $this->results [$key] ['result'] = $result;
      $this->results [$key] ['allele'] = $this->getAllele ( $result );
      $this->results [$key] ['accuracy'] = $this->getAccuracy ( $result );
      $this->results [$key] ['proteins'] = $this->getProteins ( $result );
      foreach ( $this->results [$key] ['proteins'] as $key2 => $result2 ) {
        $this->results [$key] ['proteins'] [$key2] ['protein'] = $this->getProtein ( $result2 ['full_analisis'] [2] );
        $this->results [$key] ['proteins'] [$key2] ['peptidesTable'] = $this->getTable ( $result2 ['full_analisis'] );
        $this->results [$key] ['proteins'] [$key2] ['highBinders'] = $this->getHBinders ( $this->results [$key] ['proteins'] [$key2] ['peptidesTable'] );
        $this->results [$key] ['proteins'] [$key2] ['weakBinders'] = $this->getWBinders ( $this->results [$key] ['proteins'] [$key2] ['peptidesTable'] );
      }
    }
  }
  function getHBinders($table) {
    $sb = array ();
    foreach ( $table [1] as $key => $value ) {
      $len = count ( $value );
      if ($value [$len - 1] == 'SB')
        $sb [$key] = $value;
    }
    $sb = array_values ( $sb );
    return $sb;
  }
  function getWBinders($table) {
    $wb = array ();
    foreach ( $table [1] as $key => $value ) {
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
    $pos = strpos ( $string, 'Protein ', 0 );
    $pos2 = strpos ( $string, '. ', $pos );
    $pos += 8;
    $protein = str_replace ( array (
        " ",
        "\n",
        "\r" 
    ), "", substr ( $string, $pos, $pos2 - $pos ) );
    $pos = strpos ( $protein, "_", 0 );
    if ($pos !== FALSE)
      $protein = substr ( $protein, 0, $pos );
    return $protein;
  }
  function getProteins($result) {
    $i = 0;
    $pos = 0;
    $protein = array ();
    
    while ( 1 ) {
      $pos = strpos ( $result, "-----------------------------------------------------------------------------------", $pos + 1 );
      if ($pos === FALSE)
        break;
      $pos2 = strpos ( $result, "-----------------------------------------------------------------------------------", $pos + 1 );
      if ($pos2 === FALSE)
        break;
      $pos2 = strpos ( $result, "-----------------------------------------------------------------------------------", $pos2 + 1 );
      if ($pos2 === FALSE)
        break;
      $pos2 = strpos ( $result, "-----------------------------------------------------------------------------------", $pos2 + 1 );
      if ($pos === FALSE)
        break;
      $pos2 += 83;
      $protein [$i] = array (
          'full_analisis',
          'protein',
          'highBinders',
          'weakBinders',
          'peptidesTable' 
      );
      $protein [$i] ['full_analisis'] = explode ( "-----------------------------------------------------------------------------------", substr ( $result, $pos, $pos2 - $pos ) );
      unset ( $protein [$i] ['full_analisis'] [0] );
      unset ( $protein [$i] ['full_analisis'] [4] );
      $protein [$i] ['full_analisis'] = array_values ( $protein [$i] ['full_analisis'] );
      $pos = $pos2;
      $i ++;
    }
    
    return $protein;
  }
  function getAllele($result) {
    $pos = 0;
    $pos2 = strpos ( $result, ': Estimated prediction accuracy', 0 );
    $allele = str_replace ( array (
        " ",
        "\n",
        "\r" 
    ), "", substr ( $result, $pos, $pos2 - $pos ) );
    return $allele;
  }
  function getAccuracy($result) {
    $pos = strpos ( $result, ': Estimated prediction accuracy', 0 );
    $pos2 = strpos ( $result, ' (', 0 );
    $pos += 31;
    $accuracy = str_replace ( " ", "", substr ( $result, $pos, $pos2 - $pos ) );
    return $accuracy;
  }
  function getAttr() {
    $pos = strpos ( $this->original, '# Peptide length ', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 17;
    $this->attr ['peptide_len'] = substr ( $this->original, $pos, $pos2 - $pos );
    
    $pos = strpos ( $this->original, '# Affinity Threshold for Strong binding peptides ', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 49;
    $this->attr ['thas'] = str_replace ( " ", "", substr ( $this->original, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $this->original, '# Affinity Threshold for Weak binding peptides ', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 47;
    $this->attr ['thaw'] = str_replace ( " ", "", substr ( $this->original, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $this->original, '# Rank Threshold for Strong binding peptides ', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 45;
    $this->attr ['thrs'] = str_replace ( " ", "", substr ( $this->original, $pos, $pos2 - $pos ) );
    
    $pos = strpos ( $this->original, '# Rank Threshold for Weak binding peptides ', 0 );
    $pos2 = strpos ( $this->original, "\n", $pos );
    $pos += 43;
    $this->attr ['thrw'] = str_replace ( " ", "", substr ( $this->original, $pos, $pos2 - $pos ) );
  }
  function printResults() {
    print_r ( $this->results );
  }
  function filter_bindings() {
    $filter = '';
    
    $filter .= "<b>Longitud del peptido:  </b>" . $this->attr ['peptide_len'] . "<br><br>";
    $filter .= "<b>Umbral de afinidad para uni&oacuten fuerte:  </b>" . $this->attr ['thas'] . "<br>";
    $filter .= "<b>Umbral de afinidad para uni&oacuten d&eacutebil:  </b>" . $this->attr ['thaw'] . "<br>";
    $filter .= "<b>Umbral de rango para uni&oacuten fuerte:  </b>" . $this->attr ['thrs'] . "<br>";
    $filter .= "<b>Umbral de rango para uni&oacuten d&eacutebil:  </b>" . $this->attr ['thrw'] . "<br>";
    $filter .= '<br>';
    // $filter .= "<h3>Resultados:</h3>";
    
    foreach ( $this->results as $key => $result ) {
      $filter .= "<div> <h3>" . $result ['allele'] . "  |  " . " Precisi&oacuten estimada de predicci&oacuten : " . $result ['accuracy'];
      $filter .= "</h3></div>";
      foreach ( $result ['proteins'] as $key2 => $result2 ) {
        $filter .= "<table style='width:50%;'>";
        $filter .= "<tr><td id='mhc_header'>Proteina</td>
												<td id='mhc_header'>Peptidos</td>
												<td id='mhc_header'>Uniones fuertes</td>
												<td id='mhc_header'>Uniones debiles</td>
												</tr>";
        $filter .= "<tr>";
        $filter .= "<td>" . $result2 ['protein'] . "</td>";
        $filter .= "<td>" . count ( $result2 ['peptidesTable'] [1] ) . "</td>";
        $filter .= "<td>" . count ( $result2 ['highBinders'] ) . "</td>";
        $filter .= "<td>" . count ( $result2 ['weakBinders'] ) . "</td>";
        $filter .= "</tr>";
        $filter .= "</table>";
        
        $filter .= "<table style='width:80%;'>";
        
        $filter .= "<tr>";
        foreach ( $result2 ['peptidesTable'] [0] as $key3 => $result3 )
          $filter .= "<td id='mhc_header'>$result3</td>";
        $filter .= "</tr>";
        
        foreach ( $result2 ['highBinders'] as $key3 => $result3 ) {
          $filter .= "<tr>";
          foreach ( $result3 as $key4 => $result4 )
            $filter .= "<td>$result4</td>";
          $filter .= "</tr>";
        }
        
        foreach ( $result2 ['weakBinders'] as $key3 => $result3 ) {
          $filter .= "<tr>";
          foreach ( $result3 as $key4 => $result4 )
            $filter .= "<td>$result4</td>";
          $filter .= "</tr>";
        }
        
        $filter .= "</table>";
        $filter .= '<br>';
      }
      $filter .= '<br>';
    }
    savefile("parseMHC",print_r($this->results,true));
    return $filter;
  }
}
?>
			
