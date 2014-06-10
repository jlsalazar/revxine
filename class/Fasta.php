<?php
/*
 * Fasta.php REVXINE system, Fasta class Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
class fasta {
  public $header = array (); // array(['full_line'],[param])
  public $sequence = array (); // array()
  public $count = 0;
  public $count_old = 0;
  function __construct($source) {
    $source_array = explode ( "\n", $source );
    $index = - 1;
    
    foreach ( $source_array as $line ) {
      $new_sequence = strpos ( $line, ">" );
      $new_note = strpos ( $line, ";" );
      
      if ($new_sequence === false && $new_note === false) {
        $this->sequence [$index] .= $line;
      } else {
        $index ++;
        
        $line = substr ( $line, 1 );
        
        $this->header [$index] = array (
            "full_line" => $line,
            "param" => explode ( "|", $line ) 
        );
        
        $this->sequence [$index] = '';
      }
    }
    $this->count_old = $this->count = $index + 1;
  }
  function fasta_string() {
    $content = '';
    foreach ( $this->header as $i => $line )
      $content = $content . '>' . $this->header [$i] ['full_line'] . "\n" . $this->sequence [$i] . "\n";
    return $content;
  }
  function delete_seq($index) {
    unset ( $this->header [$index] );
    unset ( $this->sequence [$index] );
    
    // $this->header = array_values($this->header);
    // $this->sequence = array_values($this->sequence);
    
    $this->count --;
  }
}
?>