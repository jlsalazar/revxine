<?php
/*
 * Cello.php REVXINE system, Cello class Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('Server.php');
include_once ('Cello_parse.php');
class Cello extends Server {
  public $patron = "/CELLO [pP]rediction:/";
  public $parse;
  public $location;
  public $reliability;
  function __construct() {
    $this->setUrl ( 'http://cello.life.nctu.edu.tw/cgi/main.cgi' );
  }
  public function setOriginal($html) {
    $patron = '/<div(.*)<\/div>/s';
    if (preg_match ( $patron, $html, $coincidencias, PREG_OFFSET_CAPTURE )) {
      $this->setContent ( 0, $coincidencias [0] [0] );
    }
  }
  public function getOriginal() {
    $content = $this->getContent ();
    return $content [0];
  }
  public function resultIsValid($html) {
    if (preg_match ( $this->patron, $html ))
      return TRUE;
    else
      return FALSE;
  }
  
  /* Metodo para facilitar la etapa de pruebas */
  public function HTTPfromfile($file) {
    $html = file_get_contents ( $file );
    $this->setOriginal ( $html );
    $this->setResult_isOK ( 0 );
  }
  public function verificateResponse($http) {
    $this->setOriginal ( $http ['html'] );
    if ($this->resultIsValid ( $this->getOriginal () ))
      $this->setResult_isOK ( 0 );
    else
      $this->setResult_isOK ( 1 );
  }
  public function AllocateParams($post) {
    $this->location = (isset ( $post ['LOCATION'] )) ? $post ['LOCATION'] : 'Extracellular';
    $this->reliability = (isset ( $post ['RELIABILITY'] )) ? $post ['RELIABILITY'] : 'normal';
  }
  public function generateStruct() {
    $this->parse = new Cello_parse ( $this->getOriginal () );
    //$code = "<pre>".print_r($this->parse->results[0],TRUE)."</pre>";
    echo $code;
  }
  public function generateFilters($fasta) {
    $this->setContent ( 1, $this->parse->filter_selection ( $fasta, $this->location, $this->reliability ) );
  }
  public function UpdateFasta($fasta) {
    $this->location;
    if ($this->reliability == 'alta') {
      foreach ( $this->parse->results as $i => $result ) {
        $band = FALSE;
        foreach ( $this->location as $j => $location )
          if ($result ['best'] ['localization'] == $location)
            $band = TRUE;
        
        if ($band == FALSE)
          $fasta->delete_seq ( $i );
      }
    } else {
      foreach ( $this->parse->results as $i => $result ) {
        $band = FALSE;
        foreach ( $this->location as $j => $location )
          foreach ( $result ['bests'] as $k => $best )
            if ($best ['localization'] == $location)
              $band = TRUE;
        
        if ($band == FALSE)
          $fasta->delete_seq ( $i );
      }
    }
    
    return $fasta;
  }
  public function printContent() {
    $print = "<div id='content_menu'>";
    foreach ( $this->getContent () as $i => $object ) {
      switch ($i) {
        case 0 :
          $title = "Original";
          break;
        case 1 :
          $title = "Selecci&oacuten";
          break;
        default :
          $title = $i;
      }
      $print = $print . "<a href='#' onclick=\"showmenu('$i', 'Cello_content'," . count ( $this->getContent () ) . ")\">" . $title . "</a> | ";
    }
    $print = $print . "</div>";
    
    $print = $print . "<div id='content'>";
    foreach ( $this->getContent () as $i => $object ) {
      if ($i == 0) {
        $print = $print . "<div id='Cello_content$i' style='display: none;'> " . $object . "</div>";
      } else {
        $print = $print . "<div id='Cello_content$i' style='display: block;'> " . $object . "</div>";
      }
    }
    $print = $print . "</div>";
    
    return $print;
  }
  public function generatePost($fasta, $e_post, $files, $boundary) {
    if (isset ( $e_post ['Cello'] )) {
      $array_Cello = array (
          'species' => $e_post ['Cello_species'],
          'seqtype' => $e_post ['Cello_seqtype'],
          // 'fasta'=>$e_post['SEQPASTE'],
          'fasta' => $fasta,
          'Submit' => 'Submit' 
      );
      
      // if(isset($files['SEQSUB']) && !empty($files['SEQSUB']['tmp_name']))
      // $array_Cello['fasta'] = file_get_contents($files['SEQSUB']['tmp_name'], true);
      $this->setPost ( multipart_build_query ( $array_Cello, $boundary ) );
    }
  }
  public function initialize() {
    $names = array (
        'seqtype' => 'Cello_seqtype',
        'species' => 'Cello_species' 
    );
    
    $this->setForm ( "
				<div align='center'>CELLO v.2.5: sub<b>CEL</b>lular <b>LO</b>calization
					predictor
				</div>
				
				<div style='color: #ffffff; background-color: #000000; width: 100%; padding-top: 1px; padding-bottom: 2px;'>&nbsp;ORGANISMS</div>
				<div> 
					<input type='radio' name='$names[species]' value='pro' >Gram negative<br>
					<input type='radio' name='$names[species]' value='gramp' checked>Gram positive<br>
					<input type='radio' name='$names[species]' value='eu'>Eukaryotes
				</div> 
				
				<div style='color: #ffffff; background-color: #000000; width: 100%; padding-top: 1px; padding-bottom: 2px;'>&nbsp;SEQUENCES</div>
				<div> 
					<input type='radio' name='$names[seqtype]' value='dna'> DNA <br> 
					<input type='radio' name='$names[seqtype]' value='prot' checked> 
					Protein<br>
					&nbsp;<br>
				</div> 
				" );
  }
}
?>
			
