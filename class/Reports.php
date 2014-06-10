<?php
/*
 * Reports.php REVXINE system, Reports class Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('class/Fasta.php');
include_once ('class/classes.php');
class Reports {
  private $servers;
  private $fasta;
  private $Content = array ();
  function __construct($servers, $fasta) {
    $this->servers = $servers;
    $this->fasta = $fasta;
    
    // $this->Content[0] = "Reporte 1";
    // $this->Content[1] = "Reporte 2";
    $this->Content [0] = (isset ( $servers ['NetMHCI'] ) && isset ( $servers ['NetMHCII'] )) ? $this->eMHCI_II () : 'No disponible.';
    ;
    // $this->Content[3] = (isset($servers['BepiPred']))?$this->NUMeCellB():'No disponible.';
    // $this->Content[4] = "Reporte 5";
    // $this->Content[5] = "Reporte 6";
    // $this->Content[6] = "Reporte 7";
    $this->Content [1] = (isset ( $servers ['NetMHCI'] ) && isset ( $servers ['NetMHCII'] )) ? $this->tMHCI_II () : 'No disponible.';
    // $this->Content[8] = (isset($servers['BepiPred']))?$this->eCellB():'No disponible.';
    $this->Content [2] = (isset ( $servers ['NetMHCI'] )) ? $this->eMHCI () : 'No disponible.';
  }
  function getResult_isOK() {
    return 0;
  }
  public function printContent() {
    $print = "<div id='content_menu'>";
    foreach ( $this->Content as $i => $object ) {
      switch ($i) {
        case 0 :
          $title = "Numero de Ep&iacutetopos(MHCI y II)";
          break;
        case 1 :
          $title = "Traslape(MHCI y II)";
          break;
        case 2 :
          $title = "Ep&iacutetopos(MHCI)";
          break;
        case 3 :
          $title = "";
          break;
        case 4 :
          $title = "Reporte_5";
          break;
        case 5 :
          $title = "Reporte_6";
          break;
        case 6 :
          $title = "Reporte_7";
          break;
        case 7 :
          $title = "";
          break;
        case 8 :
          $title = "Epitopos(Cell B)";
          break;
        case 9 :
          $title = "Reporte_10";
          break;
        default :
          $title = $i;
      }
      $print = $print . "<a href='#' onclick=\"showmenu('$i', 'Reports_content'," . count ( $this->Content ) . ")\">" . $title . "</a> | ";
    }
    $print = $print . "</div>";
    
    $print = $print . "<div id='content'>";
    foreach ( $this->Content as $i => $object ) {
      $print = $print . "<div id='Reports_content$i' style='display: none;'> " . $object . "</div>";
    }
    $print = $print . "</div>";
    
    return $print;
  }
  function eMHCI_II() {
    $servers = $this->servers;
    $MHCIparse = (isset ( $servers ['NetMHCI'] )) ? $servers ['NetMHCI']->parse : NULL;
    $MHCIIparse = (isset ( $servers ['NetMHCII'] )) ? $servers ['NetMHCII']->parse : NULL;
    
    $report = '';
    if ($MHCIparse != NULL && $MHCIIparse != NULL) {
      $report .= "Tabla. N&uacutemero de ep&iacutetopos de c&eacutelulas T (MHCI y II) estimados mediante NetMHC y NetMHCII.<br>";
      
      $report .= "<div>";
      $report .= "<table style='width:50%;'>";
      $report .= "<tr>
									<td rowspan='2' id='mhc_header'> Proteina</td>
									<td colspan='2' id='mhc_header'> Afinidad por MHC I (Tc)</td>
									<td colspan='2' id='mhc_header'> Afinidad por MHC II (Th)</td>
									</tr>";
      $report .= "<tr>
									<td id='mhc_header'> SB</td>
									<td id='mhc_header'> WB</td>
									<td id='mhc_header'> SB</td>
									<td id='mhc_header'> WB</td>
									</tr>";
      // Proteina por proteina, buscar en todos los allelos y contar los WB y WB
      $proteinsMHCI = array ();
      foreach ( $MHCIparse->results as $key => $result ) {
        foreach ( $result ['proteins'] as $key2 => $result2 ) {
          if (! isset ( $proteinsMHCI [$result2 ['protein']] ))
            $proteinsMHCI [$result2 ['protein']] = array (
                'strongBinders' => 0,
                'weakBinders' => 0 
            );
          $proteinsMHCI [$result2 ['protein']] ['strongBinders'] += count ( $result2 ['highBinders'] );
          $proteinsMHCI [$result2 ['protein']] ['weakBinders'] += count ( $result2 ['weakBinders'] );
        }
      }
      
      $proteinsMHCII = array ();
      foreach ( $MHCIIparse->results as $key => $result ) {
        if (! isset ( $proteinsMHCII [$result ['protein']] ))
          $proteinsMHCII [$result ['protein']] = array (
              'strongBinders' => 0,
              'weakBinders' => 0 
          );
        $proteinsMHCII [$result ['protein']] ['strongBinders'] += count ( $result ['strongBinders'] );
        $proteinsMHCII [$result ['protein']] ['weakBinders'] += count ( $result ['weakBinders'] );
      }
      
      foreach ( $proteinsMHCI as $key => $result ) {
        $report .= "<tr>
									<td> $key</td>
									<td> $result[strongBinders]</td>
									<td> $result[weakBinders]</td>
									<td> " . $proteinsMHCII [$key] ['strongBinders'] . " </td>
									<td> " . $proteinsMHCII [$key] ['weakBinders'] . " </td>
									</tr>";
      }
      
      $report .= "</table>";
      $report .= "</div>";
      
      $report .= "<div>";
      
      if (($MHCIparse->attr ['thas'] == $MHCIIparse->attr ['thas']) && ($MHCIparse->attr ['thaw'] == $MHCIIparse->attr ['thaw']))
        $report .= "Uni&oacuten fuerte IC50s <" . $MHCIparse->attr ['thas'] . " nM y uni&oacuten d&eacutebil IC50s <" . $MHCIparse->attr ['thaw'] . " nM para ambos MHC I y II.";
      else {
        $report .= "Uni&oacuten fuerte IC50s <" . $MHCIparse->attr ['thas'] . " nM y uni&oacuten d&eacutebil IC50s <" . $MHCIparse->attr ['thaw'] . " nM para MHC I.<br>";
        $report .= "Uni&oacuten fuerte IC50s <" . $MHCIIparse->attr ['thas'] . " nM y uni&oacuten d&eacutebil IC50s <" . $MHCIIparse->attr ['thaw'] . " nM para MHC II.<br>";
      }
      $report .= "</div>";
    }
    
    return $report;
  }
  function tMHCI_II() {
    $servers = $this->servers;
    $MHCIparse = (isset ( $servers ['NetMHCI'] )) ? $servers ['NetMHCI']->parse : NULL;
    $MHCIIparse = (isset ( $servers ['NetMHCII'] )) ? $servers ['NetMHCII']->parse : NULL;
    
    $report = '';
    if ($MHCIparse != NULL && $MHCIIparse != NULL) {
      $report .= "Tabla. Transposici&oacuten epitopos de c&eacutelulas T presentados por MHC I y MHC II.<br>";
      
      $report .= "<div>";
      $report .= "<table style='width:95%;'>";
      $report .= "<tr>
									<td rowspan='2' id='mhc_header'> Prote&iacutena</td>
									<td colspan='4' id='mhc_header'> MHC I Ep&iacutetopo Cel. T</td>
									<td colspan='4' id='mhc_header'> MHC I Ep&iacutetopo Cel. T</td>
									</tr>";
      $report .= "<tr>
									<td id='mhc_header'> Secuencia</td>
									<td id='mhc_header'> Posici&oacuten</td>
									<td id='mhc_header'> Allelo</td>
									<td id='mhc_header'> Uni&oacuten</td>
									<td id='mhc_header'> Secuencia</td>
									<td id='mhc_header'> Posici&oacuten</td>
									<td id='mhc_header'> Allelo</td>
									<td id='mhc_header'> Uni&oacuten</td>
									</tr>";
      
      // Proteina por proteina en MHCI
      
      $i = 0;
      $proteinsMHCI = array ();
      foreach ( $MHCIparse->results as $key => $result ) {
        foreach ( $result ['proteins'] as $key2 => $result2 ) {
          foreach ( $result2 ['highBinders'] as $key3 => $result3 ) {
            $proteinsMHCI [$i] = $result3;
            $i ++;
          }
          foreach ( $result2 ['weakBinders'] as $key3 => $result3 ) {
            $proteinsMHCI [$i] = $result3;
            $i ++;
          }
        }
      }
      
      $i = 0;
      $proteinsMHCII = array ();
      foreach ( $MHCIIparse->results as $key => $result ) {
        foreach ( $result ['strongBinders'] as $key2 => $result2 ) {
          $proteinsMHCII [$i] = $result2;
          $i ++;
        }
        foreach ( $result ['weakBinders'] as $key2 => $result2 ) {
          $proteinsMHCII [$i] = $result2;
          $i ++;
        }
      }
      
      $proteins = array ();
      foreach ( $MHCIIparse->results as $key => $result ) {
        if (! isset ( $proteins [$result ['protein']] ))
          $proteins [$result ['protein']] = "";
      }
      
      foreach ( $proteins as $key => $result ) {
        $report .= "<tr><td>$key</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        foreach ( $proteinsMHCI as $key2 => $result2 ) {
          if ($result2 [3] == $key) {
            $best = array ();
            foreach ( $proteinsMHCII as $key3 => $result3 ) {
              if ($result3 [3] == $key) {
                $band = strpos ( $result3 [2], $result2 [2] );
                if ($band !== FALSE) {
                  if (count ( $best ) == 0) {
                    $best = $result3;
                  } else if ($result3 [8] <= $best [8] && $result3 [7] <= $best [7] && $result3 [6] >= $best [6]) {
                    $best = $result3;
                  }
                }
              }
            }
            if (count ( $best ) != 0) {
              // imprimir el renglon de ambos
              
              $num = $result2 [0] + strlen ( $result2 [2] ) - 1;
              $num2 = $best [0] + strlen ( $best [2] ) - 1;
              
              $report .= "<tr><td></td>";
              $report .= "<td>" . $result2 [2] . "</td> <td>" . $result2 [0] . "-$num" . "</td> <td>" . $result2 [1] . "</td> <td>" . $result2 [count ( $result2 ) - 1] . "</td>";
              $report .= "<td>" . str_replace ( $result2 [2], "<u>" . $result2 [2] . "</u>", $best [2] ) . "</td> <td>" . $best [0] . "-$num2" . "" . "</td> <td>" . $best [1] . "</td> <td>" . $best [count ( $best ) - 1] . "</td>";
              $report .= "</tr>";
            }
          }
        }
        $report .= "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        // Recorremos toda la tabla MHCI
        // cuando encontremos uno llamado key nos paramos y buscamos en todoa la tabla MHCII alguno llamado
      }
      
      $report .= "</table>";
      $report .= "</div>";
    }
    
    return $report;
  }
  function eCellB() {
    $servers = $this->servers;
    $fasta = $this->fasta;
    $CellBparse = (isset ( $servers ['BepiPred'] )) ? $servers ['BepiPred']->parse : NULL;
    
    $report = '';
    if ($CellBparse != NULL) {
      $report .= "Tabla. Epitopos de c&eacutelulas B (9-aa).<br>";
      
      $report .= "<div>";
      $report .= "<table style='width:70%;'>";
      $report .= "<tr>
									<td id='mhc_header'> Prote&iacutena</td>
									<td id='mhc_header'> Secuencia</td>
									<td id='mhc_header'> Posici&oacuten inicial</td>
									<td id='mhc_header'> Puntaje</td>
									</tr>";
      
      foreach ( $CellBparse->results as $key => $value ) {
        $count = count ( $value ['epitopes'] );
        if ($count > 0) {
          $value ['seq'] = str_replace ( array (
              "\n",
              "\r" 
          ), "", $value ['seq'] );
          $report .= "<tr>";
          $report .= "<td>" . $value ['protein'] . "</td>";
          $report .= "<td>" . substr ( $value ['seq'], $value ['epitopes'] [0] [3] - 1, 9 ) . "</td>";
          $report .= "<td>" . $value ['epitopes'] [0] [3] . "</td>";
          $report .= "<td>" . $value ['epitopes'] [0] [5] . "</td>";
          $report .= "</tr>";
          unset ( $value ['epitopes'] [0] );
          foreach ( $value ['epitopes'] as $epitopes ) {
            $report .= "<tr>";
            $report .= "<td></td>";
            $report .= "<td>" . substr ( $value ['seq'], $epitopes [3] - 1, 9 ) . "</td>";
            $report .= "<td>" . $epitopes [3] . "</td>";
            $report .= "<td>" . $epitopes [5] . "</td>";
            $report .= "</tr>";
          }
        } else
          $report .= "<tr><td>$value[protein]</td><td></td><td></td><td></td></tr>";
      }
      $report .= "</table>";
      $report .= "</div>";
    }
    return $report;
  }
  function NUMeCellB() {
    $servers = $this->servers;
    $fasta = $this->fasta;
    $CellBparse = (isset ( $servers ['BepiPred'] )) ? $servers ['BepiPred']->parse : NULL;
    
    $report = '';
    if ($CellBparse != NULL) {
      $report .= "Tabla. N&uacutemero de ep&iacutetopos de c&eacutelulas B usando el servidor BepiPred
									<br>";
      
      $report .= "<div>";
      $report .= "<table style='width:50%;'>";
      $report .= "<tr>
											<td id='mhc_header'> Prote&iacutena</td>
											<td id='mhc_header'> Ep&iacutetopos Cel.B</td>
											</tr>";
      
      foreach ( $CellBparse->results as $key => $value ) {
        $count = count ( $value ['epitopes'] );
        $name = $value ['protein'];
        $report .= "<tr><td>$name</td><td>$count</td></tr>";
      }
      $report .= "</table>";
      $report .= "</div>";
    }
    return $report;
  }
  function eMHCI() {
    $servers = $this->servers;
    $fasta = $this->fasta;
    $MHCIparse = (isset ( $servers ['NetMHCI'] )) ? $servers ['NetMHCI']->parse : NULL;
    
    $report = '';
    if ($MHCIparse != NULL) {
      $report .= "Tabla. Epitopos C&eacutelulas T Citot&oacutexicas (9-aar) determinados por NetMHCI";
      
      $report .= "<div>";
      $report .= "<table style='width:40%;'>";
      $report .= "<tr>
									<td id='mhc_header'> Prote&iacutena</td>
									<td id='mhc_header'> Secuencia</td>
									<td id='mhc_header'> Alelo</td>
									<td id='mhc_header'> Posici&oacuten</td>
									</tr>";
      
      $i = 0;
      $proteinsMHCI = array ();
      foreach ( $MHCIparse->results as $key => $result ) {
        foreach ( $result ['proteins'] as $key2 => $result2 ) {
          foreach ( $result2 ['highBinders'] as $key3 => $result3 ) {
            $proteinsMHCI [$i] = $result3;
            $i ++;
          }
          foreach ( $result2 ['weakBinders'] as $key3 => $result3 ) {
            $proteinsMHCI [$i] = $result3;
            $i ++;
          }
        }
      }
      
      $proteins = array ();
      foreach ( $proteinsMHCI as $key => $result ) {
        if (! isset ( $proteins [$result [3]] ))
          $proteins [$result [3]] = array ();
        $len = count ( $proteins [$result [3]] );
        $proteins [$result [3]] [$len] = $result;
      }
      
      foreach ( $proteins as $key => $result ) {
        $report .= "<tr><td>$key</td><td></td><td></td><td></td></tr>";
        foreach ( $result as $key2 => $result2 ) {
          $report .= "<tr><td></td><td>$result2[2]</td><td>$result2[1]</td><td>$result2[0]</td></tr>";
        }
      }
      $report .= "</table>";
      $report .= "</div>";
    }
    return $report;
  }
}
?>
