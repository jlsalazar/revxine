<?php
/*
 * BepiPred.php REVXINE system, BepiPred class Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('Server.php');
include_once ('BepiPred_parse.php');
class BepiPred extends Server {
  public $patron = "/seqname[ ]+source[ ]+feature[ ]+start[ ]+end[ ]+score[ ]+N\/A[ ]+\?/";
  public $parse;
  public $score;
  function __construct() {
    $this->setUrl ( 'http://www.cbs.dtu.dk/cgi-bin/webface2.fcgi' );
  }
  public function setOriginal($html) {
    $patron = '/<pre>(.*)<\/pre>/s';
    if (preg_match ( $patron, $html, $coincidencias, PREG_OFFSET_CAPTURE ))
      $this->setContent ( 0, $coincidencias [0] [0] );
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
    $CBSstatus_patron = "/<title>Job status of [A-Z0-9]*(.*)<\/title>/";
    $CBSresult_patron = "/<title>(.*)results(.*)<\/title>/";
    
    if (preg_match ( $CBSstatus_patron, $http ['html'] )) {
      $http = waitResult ( $http, $CBSstatus_patron, 1, 5 );
      if (preg_match ( $CBSresult_patron, $http ['html'] )) {
        $this->setOriginal ( $http ['html'] );
        if ($this->resultIsValid ( $this->getOriginal () ))
          $this->setResult_isOK ( 0 );
        else
          $this->setResult_isOK ( 1 );
      } else
        $this->setResult_isOK ( 2 );
    } else
      $this->setResult_isOK ( 3 );
  }
  public function AllocateParams($post) {
    $this->score = $post ['BepiPred_threshold'];
  }
  public function generateStruct() {
    $this->parse = new BepiPred_parse ( $this->getOriginal () );
  }
  public function generateFilters($fasta) {
    $this->setContent ( 1, $this->parse->filter_score ( $this->score ) );
  }
  public function printContent() {
    $print = "<div id='content_menu'>";
    foreach ( $this->getContent () as $i => $object ) {
      switch ($i) {
        case 0 :
          $title = "Original";
          break;
        case 1 :
          $title = "Resumen";
          break;
        default :
          $title = $i;
      }
      $print = $print . "<a href='#' onclick=\"showmenu('$i', 'BepiPred_content'," . count ( $this->getContent () ) . ")\">" . $title . "</a> | ";
    }
    $print = $print . "</div>";
    
    $print = $print . "<div id='content'>";
    foreach ( $this->getContent () as $i => $object ) {
      if ($i == 0) {
        $print = $print . "<div id='BepiPred_content$i' style='display: none;'> " . $object . "</div>";
      } else {
        $print = $print . "<div id='BepiPred_content$i' style='display: block;'> " . $object . "</div>";
      }
    }
    $print = $print . "</div>";
    
    return $print;
  }
  public function generatePost($fasta, $e_post, $files, $boundary) {
    if (isset ( $e_post ['BepiPred'] ))
      $array_BepiPred = array (
          'SEQPASTE' => $fasta,
          'configfile' => $e_post ['BepiPred_configfile'],
          'threshold' => $e_post ['BepiPred_threshold'] 
      );
      // if(isset($files['SEQSUB']) && !empty($files['SEQSUB']['tmp_name']))
      // $array_BepiPred['SEQPASTE'] = file_get_contents($files['SEQSUB']['tmp_name'], true);
    $this->setPost ( multipart_build_query ( $array_BepiPred, $boundary ) );
  }
  public function initialize() {
    $names = array (
        'configfile' => 'BepiPred_configfile',
        'threshold' => 'BepiPred_threshold' 
    );
    
    $this->setForm ( "<br>
				<input  type=HIDDEN name='$names[configfile]' value='/usr/opt/www/pub/CBS/services/BepiPred-1.0/BepiPred.cf'>
				<p>
					<b>Puntuaci&oacuten de umbral para la asignaci&oacuten ep&iacutetopo</b> &nbsp;
					<input name='$names[threshold]' type='text' value='0.35' size=5>
					&nbsp; &nbsp; &nbsp;
					<br>
				</p>
		" );
  }
}
?>
