<?php
/*
 * NetMHCII.php REVXINE system, NetMHCII class Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
include_once ('Server.php');
include_once ('NetMHCII_parse.php');
class NetMHCII extends Server {
  public $patron = "/pos[ ]+Allele[ ]+peptide[ ]+Identity[ ]+Pos[ ]+Core[ ]+1-log50k\(aff\)[ ]+Affinity\(nM\)[ ]+%Rank[ ]+BindingLevel/";
  public $parse;
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
    
    if (preg_match ( $CBSstatus_patron, $http ['html'] ) || preg_match ( $CBSresult_patron, $http ['html'] )) {
      $http = waitResult ( $http, $CBSstatus_patron, 10, 15 );
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
  }
  public function generateStruct() {
    $this->parse = new NetMHCII_parse ( $this->getOriginal () );
  }
  public function generateFilters($fasta) {
    $this->setContent ( 1, $this->parse->filter_bindings () );
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
      $print = $print . "<a href='#' onclick=\"showmenu('$i', 'NetMHCII_content'," . count ( $this->getContent () ) . ")\">" . $title . "</a> | ";
    }
    $print = $print . "</div>";
    
    $print = $print . "<div id='content'>";
    foreach ( $this->getContent () as $i => $object ) {
      if ($i == 0) {
        $print = $print . "<div id='NetMHCII_content$i' style='display: none;'> " . $object . "</div>";
      } else {
        $print = $print . "<div id='NetMHCII_content$i' style='display: block;'> " . $object . "</div>";
      }
    }
    $print = $print . "</div>";
    
    return $print;
  }
  public function generatePost($fasta, $e_post, $files, $boundary) {
    $array_NetMHCII;
    if (isset ( $e_post ['NetMHCII'] ))
      $array_NetMHCII = array (
          'inp' => $e_post ['inp'],
          'SEQPASTE' => $fasta,
          // 'SEQSUB' => $e_post['SEQSUB'],
          'configfile' => $e_post ['NetMHCII_configfile'],
          'length' => $e_post ['NetMHCII_length'],
          'master' => $e_post ['NetMHCII_master'],
          'slaveA' => (isset ( $e_post ['NetMHCII_slaveA'] )) ? $e_post ['NetMHCII_slaveA'] : '',
          'slaveB' => (isset ( $e_post ['NetMHCII_slaveB'] )) ? $e_post ['NetMHCII_slaveB'] : '',
          'allele' => $e_post ['NetMHCII_allele'],
          'MHCSEQPASTEa' => $e_post ['NetMHCII_MHCSEQPASTEa'],
          // 'MHCSEQSUBa' => $e_post['NetMHCII_MHCSEQSUBa'],
          'MHCSEQPASTEb' => $e_post ['NetMHCII_MHCSEQPASTEb'],
          // 'MHCSEQSUBb'=> $e_post['NetMHCII_MHCSEQSUBb'],
          'thrs' => $e_post ['NetMHCII_thrs'],
          'thas' => $e_post ['NetMHCII_thas'],
          'thrw' => $e_post ['NetMHCII_thrw'],
          'thaw' => $e_post ['NetMHCII_thaw'],
          'filt' => $e_post ['NetMHCII_filt'],
          'thrf' => $e_post ['NetMHCII_thrf'],
          'thaf' => $e_post ['NetMHCII_thaf'],
          'fast' => (isset ( $e_post ['NetMHCII_fast'] )) ? $e_post ['NetMHCII_fast'] : '',
          'unique' => (isset ( $e_post ['NetMHCII_unique'] )) ? $e_post ['NetMHCII_unique'] : '',
          'sort' => 'on' 
      );
      
      // if(isset($files['SEQSUB']) && !empty($files['SEQSUB']['tmp_name']))
      // $array_NetMHCII['SEQPASTE'] = file_get_contents($files['SEQSUB']['tmp_name'], true);
    if (isset ( $files ['NetMHCII_MHCSEQSUBa'] ) && ! empty ( $files ['NetMHCII_MHCSEQSUBa'] ['tmp_name'] ))
      $array_NetMHCII ['MHCSEQPASTEa'] = file_get_contents ( $files ['NetMHCII_MHCSEQSUBa'] ['tmp_name'], true );
    if (isset ( $files ['NetMHCII_MHCSEQSUBb'] ) && ! empty ( $files ['NetMHCII_MHCSEQSUBb'] ['tmp_name'] ))
      $array_NetMHCII ['MHCSEQPASTEb'] = file_get_contents ( $files ['NetMHCII_MHCSEQSUBb'] ['tmp_name'], true );
    
    $this->setPost ( multipart_build_query ( $array_NetMHCII, $boundary ) );
  }
  public function initialize() {
    $names = array (
        'configfile' => 'NetMHCII_configfile',
        'length' => 'NetMHCII_length',
        'master' => 'NetMHCII_master',
        'slaveA' => 'NetMHCII_slaveA',
        'slaveB' => 'NetMHCII_slaveB',
        'allele' => 'NetMHCII_allele',
        'MHCSEQPASTEa' => 'NetMHCII_MHCSEQPASTEa',
        'MHCSEQSUBa' => 'NetMHCII_MHCSEQSUBa',
        'MHCSEQPASTEb' => 'NetMHCII_MHCSEQPASTEb',
        'MHCSEQSUBb' => 'NetMHCII_MHCSEQSUBb',
        'thrs' => 'NetMHCII_thrs',
        'thas' => 'NetMHCII_thas',
        'thrw' => 'NetMHCII_thrw',
        'thaw' => 'NetMHCII_thaw',
        'filt' => 'NetMHCII_filt',
        'thrf' => 'NetMHCII_thrf',
        'thaf' => 'NetMHCII_thaf',
        'fast' => 'NetMHCII_fast',
        'unique' => 'NetMHCII_unique' 
    );
    
    $ids = array (
        'slave0' => 'NetMHCII_slave0',
        'fullalpha' => 'NetMHCII_fullalpha',
        'slaveC0' => 'NetMHCII_slaveC0',
        'slaveC1' => 'NetMHCII_slaveC1',
        'slave_B6' => 'NetMHCII_slave_B6',
        'slave_B5' => 'NetMHCII_slave_B5',
        'slave_B4' => 'NetMHCII_slave_B4',
        'slave_B3' => 'NetMHCII_slave_B3',
        'slave_B2' => 'NetMHCII_slave_B2',
        'slaveBDQ' => 'NetMHCII_slaveBDQ',
        'slave_B1' => 'NetMHCII_slave_B1',
        'slaveADQ' => 'NetMHCII_slaveADQ',
        'slave_A1' => 'NetMHCII_slave_A1',
        'slaveBDP' => 'NetMHCII_slaveBDP',
        'slave_B0' => 'NetMHCII_slave_B0',
        'slaveADP' => 'NetMHCII_slaveADP',
        'slave_A0' => 'NetMHCII_slave_A0' 
    );
    
    $this->setForm ( "
				<br>
<input type='HIDDEN' name='$names[configfile]' value='/usr/opt/www/pub/CBS/services/NetMHCIIpan-3.0/NetMHCIIpan-3.0.cf'>

<script>

function NetMHCII_showselect(inp, divname, divname2) {
  //console.log(inp, divname, divname2);
  var slavediv;
  var i;
  // hide all blocks
  for (i=0;i<inp.length;i++){
    slavediv=document.getElementById(divname+i);
    slavediv.style.display='none';
    if (document.getElementById(divname2+i) != null) { // hide second divs too
      document.getElementById(divname2+i).style.display='none';
    }
  }
  // show chosen block
  slavediv=document.getElementById(divname+inp.selectedIndex);
  slavediv.style.display='inline';
  //console.log(divname2+inp.selectedIndex);
  if (document.getElementById(divname2+inp.selectedIndex) != null) { // show second divs too
     document.getElementById(divname2+inp.selectedIndex).style.display='inline';
  }
}     

/* Added Sep 13 2010 by Ulrik Plesner Jacobsen : Modified by Martin*/
function NetMHCII_addAlleleAB(group) {
   //console.log('$names[slaveA]'+group, '$names[slaveB]'+group);
   group = group? group: ''; // Default value of group is ''
   alleleText = document.getElementsByName('$names[allele]')[0];
   chainA = document.getElementById('$names[slaveA]'+group);
   chainB = document.getElementById('$names[slaveB]'+group);
   //console.log(alleleText, chainA, chainB);
   if (chainA.selectedIndex > -1 && chainB.selectedIndex > -1) {
      // Combine chain names
      allelename = 'HLA-'+ chainA.value +'-'+ chainB.value;
      // add name to allele list
      if (alleleText.value == '') {
      alleleText.value = allelename;
    } else {
      alleleText.value += ',' + allelename;
    }
      // Remove selection
      chainA.selectedIndex = -1;
      chainB.selectedIndex = -1;
   }
   // Cleanup allelelist
  NetMHCII_cleanList();
}

function NetMHCII_addAlleleB(element) {
  alleleText = document.getElementsByName('$names[allele]')[0];
  
  for (i = 0; i < element.options.length; i++) {
    if (element.options[i].selected) {
      alleleValue = element.options[i].value;
      
      if (alleleText.value == '') {
        alleleText.value = alleleValue;
      } else {
        alleleText.value += ',' + alleleValue;
      }
    }
  }
  
  NetMHCII_cleanList();
}


function NetMHCII_cleanList() {
  alleleText = document.getElementsByName('$names[allele]')[0];
  alleleConfirmed = new Array();
  alleleTextFinal = '';
  
  alleleList = alleleText.value.split(',');
   if(alleleText.value == ''){ return; } // Added by Martin 020513

  for (i in alleleList) {
    if (alleleList[i].indexOf(' ') == 0) {
      alleleList[i] = alleleList[i].slice(1);
    }
    
    if (!alleleConfirmed[alleleList[i]]) {
      alleleConfirmed[alleleList[i]] = 1;
    }
  }
   for (i in alleleConfirmed) {
      //console.log(i.substring(0, 4));
      if (i.substring(0, 4) == 'HLA-') { // i.indexOf('-') !== -1
         // Split in alpha and beta chain
         tmp=i.split('-');
         a = tmp[1];
         b = tmp[2];
         if (NetMHCII_allelesValid[a] && NetMHCII_allelesValid[b]) {
            alleleTextFinal += i + ',';
         } else {
            alert('No such molecule available for NetMHCIIpan: ' + i);
         }
      }else{
         // only one chain
         if (NetMHCII_allelesValid[i]) {
            alleleTextFinal += i + ',';
         } else {
            alert('No such molecule available for NetMHCIIpan: ' + i);
         }
      }
   }
  
  alleleTextFinal = alleleTextFinal.slice(0,-1);
  
  alleleText.value = alleleTextFinal;
}

function NetMHCII_showselect2(inp, divname) {
  var slavediv;
  var i;
  for (i=0;i<inp.length;i++){
    slavediv=document.getElementById(divname+i);
    slavediv.style.display='none';
  }
  slavediv=document.getElementById(divname+inp.selectedIndex);
  slavediv.style.display='block';
}

function NetMHCII_showfullalpha(group) {
  alphachainupload = document.getElementById('$ids[fullalpha]');
  console.log(group);
  if (group == 1 || group == 2 || group == 7) {
    // Show Alpha chain upload
    alphachainupload.style.display='block';
  }else{
    // Hide Alpha chain upload
    alphachainupload.style.display='none';
  }
}

</script>
   
<b>Longitud del P&eacuteptido</b> &nbsp; <input name='$names[length]' type='text' value='15' size='5'>

<script>

var NetMHCII_allelesValid = new Array ();

NetMHCII_allelesValid['DPA10103'] = 1;
NetMHCII_allelesValid['DPA10104'] = 1;
NetMHCII_allelesValid['DPA10105'] = 1;
NetMHCII_allelesValid['DPA10106'] = 1;
NetMHCII_allelesValid['DPA10107'] = 1;
NetMHCII_allelesValid['DPA10108'] = 1;
NetMHCII_allelesValid['DPA10109'] = 1;
NetMHCII_allelesValid['DPA10110'] = 1;
NetMHCII_allelesValid['DPA10201'] = 1;
NetMHCII_allelesValid['DPA10202'] = 1;
NetMHCII_allelesValid['DPA10203'] = 1;
NetMHCII_allelesValid['DPA10204'] = 1;
NetMHCII_allelesValid['DPA10301'] = 1;
NetMHCII_allelesValid['DPA10302'] = 1;
NetMHCII_allelesValid['DPA10303'] = 1;
NetMHCII_allelesValid['DPA10401'] = 1;
NetMHCII_allelesValid['DPB10101'] = 1;
NetMHCII_allelesValid['DPB10201'] = 1;
NetMHCII_allelesValid['DPB10202'] = 1;
NetMHCII_allelesValid['DPB10301'] = 1;
NetMHCII_allelesValid['DPB10401'] = 1;
NetMHCII_allelesValid['DPB10402'] = 1;
NetMHCII_allelesValid['DPB10501'] = 1;
NetMHCII_allelesValid['DPB10601'] = 1;
NetMHCII_allelesValid['DPB10801'] = 1;
NetMHCII_allelesValid['DPB110001'] = 1;
NetMHCII_allelesValid['DPB11001'] = 1;
NetMHCII_allelesValid['DPB110101'] = 1;
NetMHCII_allelesValid['DPB110201'] = 1;
NetMHCII_allelesValid['DPB110301'] = 1;
NetMHCII_allelesValid['DPB110401'] = 1;
NetMHCII_allelesValid['DPB110501'] = 1;
NetMHCII_allelesValid['DPB110601'] = 1;
NetMHCII_allelesValid['DPB110701'] = 1;
NetMHCII_allelesValid['DPB110801'] = 1;
NetMHCII_allelesValid['DPB110901'] = 1;
NetMHCII_allelesValid['DPB111001'] = 1;
NetMHCII_allelesValid['DPB11101'] = 1;
NetMHCII_allelesValid['DPB111101'] = 1;
NetMHCII_allelesValid['DPB111201'] = 1;
NetMHCII_allelesValid['DPB111301'] = 1;
NetMHCII_allelesValid['DPB111401'] = 1;
NetMHCII_allelesValid['DPB111501'] = 1;
NetMHCII_allelesValid['DPB111601'] = 1;
NetMHCII_allelesValid['DPB111701'] = 1;
NetMHCII_allelesValid['DPB111801'] = 1;
NetMHCII_allelesValid['DPB111901'] = 1;
NetMHCII_allelesValid['DPB112101'] = 1;
NetMHCII_allelesValid['DPB112201'] = 1;
NetMHCII_allelesValid['DPB112301'] = 1;
NetMHCII_allelesValid['DPB112401'] = 1;
NetMHCII_allelesValid['DPB112501'] = 1;
NetMHCII_allelesValid['DPB112601'] = 1;
NetMHCII_allelesValid['DPB112701'] = 1;
NetMHCII_allelesValid['DPB112801'] = 1;
NetMHCII_allelesValid['DPB112901'] = 1;
NetMHCII_allelesValid['DPB113001'] = 1;
NetMHCII_allelesValid['DPB11301'] = 1;
NetMHCII_allelesValid['DPB113101'] = 1;
NetMHCII_allelesValid['DPB113201'] = 1;
NetMHCII_allelesValid['DPB113301'] = 1;
NetMHCII_allelesValid['DPB113401'] = 1;
NetMHCII_allelesValid['DPB11401'] = 1;
NetMHCII_allelesValid['DPB11501'] = 1;
NetMHCII_allelesValid['DPB11601'] = 1;
NetMHCII_allelesValid['DPB11701'] = 1;
NetMHCII_allelesValid['DPB11801'] = 1;
NetMHCII_allelesValid['DPB11901'] = 1;
NetMHCII_allelesValid['DPB12001'] = 1;
NetMHCII_allelesValid['DPB12101'] = 1;
NetMHCII_allelesValid['DPB12201'] = 1;
NetMHCII_allelesValid['DPB12301'] = 1;
NetMHCII_allelesValid['DPB12401'] = 1;
NetMHCII_allelesValid['DPB12501'] = 1;
NetMHCII_allelesValid['DPB12601'] = 1;
NetMHCII_allelesValid['DPB12701'] = 1;
NetMHCII_allelesValid['DPB12801'] = 1;
NetMHCII_allelesValid['DPB12901'] = 1;
NetMHCII_allelesValid['DPB13001'] = 1;
NetMHCII_allelesValid['DPB13101'] = 1;
NetMHCII_allelesValid['DPB13201'] = 1;
NetMHCII_allelesValid['DPB13301'] = 1;
NetMHCII_allelesValid['DPB13401'] = 1;
NetMHCII_allelesValid['DPB13501'] = 1;
NetMHCII_allelesValid['DPB13601'] = 1;
NetMHCII_allelesValid['DPB13701'] = 1;
NetMHCII_allelesValid['DPB13801'] = 1;
NetMHCII_allelesValid['DPB13901'] = 1;
NetMHCII_allelesValid['DPB14001'] = 1;
NetMHCII_allelesValid['DPB14101'] = 1;
NetMHCII_allelesValid['DPB14401'] = 1;
NetMHCII_allelesValid['DPB14501'] = 1;
NetMHCII_allelesValid['DPB14601'] = 1;
NetMHCII_allelesValid['DPB14701'] = 1;
NetMHCII_allelesValid['DPB14801'] = 1;
NetMHCII_allelesValid['DPB14901'] = 1;
NetMHCII_allelesValid['DPB15001'] = 1;
NetMHCII_allelesValid['DPB15101'] = 1;
NetMHCII_allelesValid['DPB15201'] = 1;
NetMHCII_allelesValid['DPB15301'] = 1;
NetMHCII_allelesValid['DPB15401'] = 1;
NetMHCII_allelesValid['DPB15501'] = 1;
NetMHCII_allelesValid['DPB15601'] = 1;
NetMHCII_allelesValid['DPB15801'] = 1;
NetMHCII_allelesValid['DPB15901'] = 1;
NetMHCII_allelesValid['DPB16001'] = 1;
NetMHCII_allelesValid['DPB16201'] = 1;
NetMHCII_allelesValid['DPB16301'] = 1;
NetMHCII_allelesValid['DPB16501'] = 1;
NetMHCII_allelesValid['DPB16601'] = 1;
NetMHCII_allelesValid['DPB16701'] = 1;
NetMHCII_allelesValid['DPB16801'] = 1;
NetMHCII_allelesValid['DPB16901'] = 1;
NetMHCII_allelesValid['DPB17001'] = 1;
NetMHCII_allelesValid['DPB17101'] = 1;
NetMHCII_allelesValid['DPB17201'] = 1;
NetMHCII_allelesValid['DPB17301'] = 1;
NetMHCII_allelesValid['DPB17401'] = 1;
NetMHCII_allelesValid['DPB17501'] = 1;
NetMHCII_allelesValid['DPB17601'] = 1;
NetMHCII_allelesValid['DPB17701'] = 1;
NetMHCII_allelesValid['DPB17801'] = 1;
NetMHCII_allelesValid['DPB17901'] = 1;
NetMHCII_allelesValid['DPB18001'] = 1;
NetMHCII_allelesValid['DPB18101'] = 1;
NetMHCII_allelesValid['DPB18201'] = 1;
NetMHCII_allelesValid['DPB18301'] = 1;
NetMHCII_allelesValid['DPB18401'] = 1;
NetMHCII_allelesValid['DPB18501'] = 1;
NetMHCII_allelesValid['DPB18601'] = 1;
NetMHCII_allelesValid['DPB18701'] = 1;
NetMHCII_allelesValid['DPB18801'] = 1;
NetMHCII_allelesValid['DPB18901'] = 1;
NetMHCII_allelesValid['DPB19001'] = 1;
NetMHCII_allelesValid['DPB19101'] = 1;
NetMHCII_allelesValid['DPB19201'] = 1;
NetMHCII_allelesValid['DPB19301'] = 1;
NetMHCII_allelesValid['DPB19401'] = 1;
NetMHCII_allelesValid['DPB19501'] = 1;
NetMHCII_allelesValid['DPB19601'] = 1;
NetMHCII_allelesValid['DPB19701'] = 1;
NetMHCII_allelesValid['DPB19801'] = 1;
NetMHCII_allelesValid['DPB19901'] = 1;
NetMHCII_allelesValid['DPB10901'] = 1;
NetMHCII_allelesValid['DQA10101'] = 1;
NetMHCII_allelesValid['DQA10102'] = 1;
NetMHCII_allelesValid['DQA10103'] = 1;
NetMHCII_allelesValid['DQA10104'] = 1;
NetMHCII_allelesValid['DQA10105'] = 1;
NetMHCII_allelesValid['DQA10106'] = 1;
NetMHCII_allelesValid['DQA10107'] = 1;
NetMHCII_allelesValid['DQA10108'] = 1;
NetMHCII_allelesValid['DQA10109'] = 1;
NetMHCII_allelesValid['DQA10201'] = 1;
NetMHCII_allelesValid['DQA10301'] = 1;
NetMHCII_allelesValid['DQA10302'] = 1;
NetMHCII_allelesValid['DQA10303'] = 1;
NetMHCII_allelesValid['DQA10401'] = 1;
NetMHCII_allelesValid['DQA10402'] = 1;
NetMHCII_allelesValid['DQA10404'] = 1;
NetMHCII_allelesValid['DQA10501'] = 1;
NetMHCII_allelesValid['DQA10503'] = 1;
NetMHCII_allelesValid['DQA10504'] = 1;
NetMHCII_allelesValid['DQA10505'] = 1;
NetMHCII_allelesValid['DQA10506'] = 1;
NetMHCII_allelesValid['DQA10507'] = 1;
NetMHCII_allelesValid['DQA10508'] = 1;
NetMHCII_allelesValid['DQA10509'] = 1;
NetMHCII_allelesValid['DQA10510'] = 1;
NetMHCII_allelesValid['DQA10511'] = 1;
NetMHCII_allelesValid['DQA10601'] = 1;
NetMHCII_allelesValid['DQA10602'] = 1;
NetMHCII_allelesValid['DQB10201'] = 1;
NetMHCII_allelesValid['DQB10202'] = 1;
NetMHCII_allelesValid['DQB10203'] = 1;
NetMHCII_allelesValid['DQB10204'] = 1;
NetMHCII_allelesValid['DQB10205'] = 1;
NetMHCII_allelesValid['DQB10206'] = 1;
NetMHCII_allelesValid['DQB10301'] = 1;
NetMHCII_allelesValid['DQB10302'] = 1;
NetMHCII_allelesValid['DQB10303'] = 1;
NetMHCII_allelesValid['DQB10304'] = 1;
NetMHCII_allelesValid['DQB10305'] = 1;
NetMHCII_allelesValid['DQB10306'] = 1;
NetMHCII_allelesValid['DQB10307'] = 1;
NetMHCII_allelesValid['DQB10308'] = 1;
NetMHCII_allelesValid['DQB10309'] = 1;
NetMHCII_allelesValid['DQB10310'] = 1;
NetMHCII_allelesValid['DQB10311'] = 1;
NetMHCII_allelesValid['DQB10312'] = 1;
NetMHCII_allelesValid['DQB10313'] = 1;
NetMHCII_allelesValid['DQB10314'] = 1;
NetMHCII_allelesValid['DQB10315'] = 1;
NetMHCII_allelesValid['DQB10316'] = 1;
NetMHCII_allelesValid['DQB10317'] = 1;
NetMHCII_allelesValid['DQB10318'] = 1;
NetMHCII_allelesValid['DQB10319'] = 1;
NetMHCII_allelesValid['DQB10320'] = 1;
NetMHCII_allelesValid['DQB10321'] = 1;
NetMHCII_allelesValid['DQB10322'] = 1;
NetMHCII_allelesValid['DQB10323'] = 1;
NetMHCII_allelesValid['DQB10324'] = 1;
NetMHCII_allelesValid['DQB10325'] = 1;
NetMHCII_allelesValid['DQB10326'] = 1;
NetMHCII_allelesValid['DQB10327'] = 1;
NetMHCII_allelesValid['DQB10328'] = 1;
NetMHCII_allelesValid['DQB10329'] = 1;
NetMHCII_allelesValid['DQB10330'] = 1;
NetMHCII_allelesValid['DQB10331'] = 1;
NetMHCII_allelesValid['DQB10332'] = 1;
NetMHCII_allelesValid['DQB10333'] = 1;
NetMHCII_allelesValid['DQB10334'] = 1;
NetMHCII_allelesValid['DQB10335'] = 1;
NetMHCII_allelesValid['DQB10336'] = 1;
NetMHCII_allelesValid['DQB10337'] = 1;
NetMHCII_allelesValid['DQB10338'] = 1;
NetMHCII_allelesValid['DQB10401'] = 1;
NetMHCII_allelesValid['DQB10402'] = 1;
NetMHCII_allelesValid['DQB10403'] = 1;
NetMHCII_allelesValid['DQB10404'] = 1;
NetMHCII_allelesValid['DQB10405'] = 1;
NetMHCII_allelesValid['DQB10406'] = 1;
NetMHCII_allelesValid['DQB10407'] = 1;
NetMHCII_allelesValid['DQB10408'] = 1;
NetMHCII_allelesValid['DQB10501'] = 1;
NetMHCII_allelesValid['DQB10502'] = 1;
NetMHCII_allelesValid['DQB10503'] = 1;
NetMHCII_allelesValid['DQB10505'] = 1;
NetMHCII_allelesValid['DQB10506'] = 1;
NetMHCII_allelesValid['DQB10507'] = 1;
NetMHCII_allelesValid['DQB10508'] = 1;
NetMHCII_allelesValid['DQB10509'] = 1;
NetMHCII_allelesValid['DQB10510'] = 1;
NetMHCII_allelesValid['DQB10511'] = 1;
NetMHCII_allelesValid['DQB10512'] = 1;
NetMHCII_allelesValid['DQB10513'] = 1;
NetMHCII_allelesValid['DQB10514'] = 1;
NetMHCII_allelesValid['DQB10601'] = 1;
NetMHCII_allelesValid['DQB10602'] = 1;
NetMHCII_allelesValid['DQB10603'] = 1;
NetMHCII_allelesValid['DQB10604'] = 1;
NetMHCII_allelesValid['DQB10607'] = 1;
NetMHCII_allelesValid['DQB10608'] = 1;
NetMHCII_allelesValid['DQB10609'] = 1;
NetMHCII_allelesValid['DQB10610'] = 1;
NetMHCII_allelesValid['DQB10611'] = 1;
NetMHCII_allelesValid['DQB10612'] = 1;
NetMHCII_allelesValid['DQB10614'] = 1;
NetMHCII_allelesValid['DQB10615'] = 1;
NetMHCII_allelesValid['DQB10616'] = 1;
NetMHCII_allelesValid['DQB10617'] = 1;
NetMHCII_allelesValid['DQB10618'] = 1;
NetMHCII_allelesValid['DQB10619'] = 1;
NetMHCII_allelesValid['DQB10621'] = 1;
NetMHCII_allelesValid['DQB10622'] = 1;
NetMHCII_allelesValid['DQB10623'] = 1;
NetMHCII_allelesValid['DQB10624'] = 1;
NetMHCII_allelesValid['DQB10625'] = 1;
NetMHCII_allelesValid['DQB10627'] = 1;
NetMHCII_allelesValid['DQB10628'] = 1;
NetMHCII_allelesValid['DQB10629'] = 1;
NetMHCII_allelesValid['DQB10630'] = 1;
NetMHCII_allelesValid['DQB10631'] = 1;
NetMHCII_allelesValid['DQB10632'] = 1;
NetMHCII_allelesValid['DQB10633'] = 1;
NetMHCII_allelesValid['DQB10634'] = 1;
NetMHCII_allelesValid['DQB10635'] = 1;
NetMHCII_allelesValid['DQB10636'] = 1;
NetMHCII_allelesValid['DQB10637'] = 1;
NetMHCII_allelesValid['DQB10638'] = 1;
NetMHCII_allelesValid['DQB10639'] = 1;
NetMHCII_allelesValid['DQB10640'] = 1;
NetMHCII_allelesValid['DQB10641'] = 1;
NetMHCII_allelesValid['DQB10642'] = 1;
NetMHCII_allelesValid['DQB10643'] = 1;
NetMHCII_allelesValid['DQB10644'] = 1;
NetMHCII_allelesValid['DRB1_0101'] = 1;
NetMHCII_allelesValid['DRB1_0102'] = 1;
NetMHCII_allelesValid['DRB1_0103'] = 1;
NetMHCII_allelesValid['DRB1_0104'] = 1;
NetMHCII_allelesValid['DRB1_0105'] = 1;
NetMHCII_allelesValid['DRB1_0106'] = 1;
NetMHCII_allelesValid['DRB1_0107'] = 1;
NetMHCII_allelesValid['DRB1_0108'] = 1;
NetMHCII_allelesValid['DRB1_0109'] = 1;
NetMHCII_allelesValid['DRB1_0110'] = 1;
NetMHCII_allelesValid['DRB1_0111'] = 1;
NetMHCII_allelesValid['DRB1_0112'] = 1;
NetMHCII_allelesValid['DRB1_0113'] = 1;
NetMHCII_allelesValid['DRB1_0114'] = 1;
NetMHCII_allelesValid['DRB1_0115'] = 1;
NetMHCII_allelesValid['DRB1_0116'] = 1;
NetMHCII_allelesValid['DRB1_0117'] = 1;
NetMHCII_allelesValid['DRB1_0118'] = 1;
NetMHCII_allelesValid['DRB1_0119'] = 1;
NetMHCII_allelesValid['DRB1_0120'] = 1;
NetMHCII_allelesValid['DRB1_0121'] = 1;
NetMHCII_allelesValid['DRB1_0122'] = 1;
NetMHCII_allelesValid['DRB1_0123'] = 1;
NetMHCII_allelesValid['DRB1_0124'] = 1;
NetMHCII_allelesValid['DRB1_0125'] = 1;
NetMHCII_allelesValid['DRB1_0126'] = 1;
NetMHCII_allelesValid['DRB1_0127'] = 1;
NetMHCII_allelesValid['DRB1_0128'] = 1;
NetMHCII_allelesValid['DRB1_0129'] = 1;
NetMHCII_allelesValid['DRB1_0130'] = 1;
NetMHCII_allelesValid['DRB1_0131'] = 1;
NetMHCII_allelesValid['DRB1_0132'] = 1;
NetMHCII_allelesValid['DRB1_0301'] = 1;
NetMHCII_allelesValid['DRB1_0302'] = 1;
NetMHCII_allelesValid['DRB1_0303'] = 1;
NetMHCII_allelesValid['DRB1_0304'] = 1;
NetMHCII_allelesValid['DRB1_0305'] = 1;
NetMHCII_allelesValid['DRB1_0306'] = 1;
NetMHCII_allelesValid['DRB1_0307'] = 1;
NetMHCII_allelesValid['DRB1_0308'] = 1;
NetMHCII_allelesValid['DRB1_0310'] = 1;
NetMHCII_allelesValid['DRB1_0311'] = 1;
NetMHCII_allelesValid['DRB1_0313'] = 1;
NetMHCII_allelesValid['DRB1_0314'] = 1;
NetMHCII_allelesValid['DRB1_0315'] = 1;
NetMHCII_allelesValid['DRB1_0317'] = 1;
NetMHCII_allelesValid['DRB1_0318'] = 1;
NetMHCII_allelesValid['DRB1_0319'] = 1;
NetMHCII_allelesValid['DRB1_0320'] = 1;
NetMHCII_allelesValid['DRB1_0321'] = 1;
NetMHCII_allelesValid['DRB1_0322'] = 1;
NetMHCII_allelesValid['DRB1_0323'] = 1;
NetMHCII_allelesValid['DRB1_0324'] = 1;
NetMHCII_allelesValid['DRB1_0325'] = 1;
NetMHCII_allelesValid['DRB1_0326'] = 1;
NetMHCII_allelesValid['DRB1_0327'] = 1;
NetMHCII_allelesValid['DRB1_0328'] = 1;
NetMHCII_allelesValid['DRB1_0329'] = 1;
NetMHCII_allelesValid['DRB1_0330'] = 1;
NetMHCII_allelesValid['DRB1_0331'] = 1;
NetMHCII_allelesValid['DRB1_0332'] = 1;
NetMHCII_allelesValid['DRB1_0333'] = 1;
NetMHCII_allelesValid['DRB1_0334'] = 1;
NetMHCII_allelesValid['DRB1_0335'] = 1;
NetMHCII_allelesValid['DRB1_0336'] = 1;
NetMHCII_allelesValid['DRB1_0337'] = 1;
NetMHCII_allelesValid['DRB1_0338'] = 1;
NetMHCII_allelesValid['DRB1_0339'] = 1;
NetMHCII_allelesValid['DRB1_0340'] = 1;
NetMHCII_allelesValid['DRB1_0341'] = 1;
NetMHCII_allelesValid['DRB1_0342'] = 1;
NetMHCII_allelesValid['DRB1_0343'] = 1;
NetMHCII_allelesValid['DRB1_0344'] = 1;
NetMHCII_allelesValid['DRB1_0345'] = 1;
NetMHCII_allelesValid['DRB1_0346'] = 1;
NetMHCII_allelesValid['DRB1_0347'] = 1;
NetMHCII_allelesValid['DRB1_0348'] = 1;
NetMHCII_allelesValid['DRB1_0349'] = 1;
NetMHCII_allelesValid['DRB1_0350'] = 1;
NetMHCII_allelesValid['DRB1_0351'] = 1;
NetMHCII_allelesValid['DRB1_0352'] = 1;
NetMHCII_allelesValid['DRB1_0353'] = 1;
NetMHCII_allelesValid['DRB1_0354'] = 1;
NetMHCII_allelesValid['DRB1_0355'] = 1;
NetMHCII_allelesValid['DRB1_0401'] = 1;
NetMHCII_allelesValid['DRB1_0402'] = 1;
NetMHCII_allelesValid['DRB1_0403'] = 1;
NetMHCII_allelesValid['DRB1_0404'] = 1;
NetMHCII_allelesValid['DRB1_0405'] = 1;
NetMHCII_allelesValid['DRB1_0406'] = 1;
NetMHCII_allelesValid['DRB1_0407'] = 1;
NetMHCII_allelesValid['DRB1_0408'] = 1;
NetMHCII_allelesValid['DRB1_0409'] = 1;
NetMHCII_allelesValid['DRB1_0410'] = 1;
NetMHCII_allelesValid['DRB1_0411'] = 1;
NetMHCII_allelesValid['DRB1_0412'] = 1;
NetMHCII_allelesValid['DRB1_0413'] = 1;
NetMHCII_allelesValid['DRB1_0414'] = 1;
NetMHCII_allelesValid['DRB1_0415'] = 1;
NetMHCII_allelesValid['DRB1_0416'] = 1;
NetMHCII_allelesValid['DRB1_0417'] = 1;
NetMHCII_allelesValid['DRB1_0418'] = 1;
NetMHCII_allelesValid['DRB1_0419'] = 1;
NetMHCII_allelesValid['DRB1_0423'] = 1;
NetMHCII_allelesValid['DRB1_0424'] = 1;
NetMHCII_allelesValid['DRB1_0426'] = 1;
NetMHCII_allelesValid['DRB1_0427'] = 1;
NetMHCII_allelesValid['DRB1_0428'] = 1;
NetMHCII_allelesValid['DRB1_0429'] = 1;
NetMHCII_allelesValid['DRB1_0430'] = 1;
NetMHCII_allelesValid['DRB1_0431'] = 1;
NetMHCII_allelesValid['DRB1_0433'] = 1;
NetMHCII_allelesValid['DRB1_0434'] = 1;
NetMHCII_allelesValid['DRB1_0435'] = 1;
NetMHCII_allelesValid['DRB1_0436'] = 1;
NetMHCII_allelesValid['DRB1_0437'] = 1;
NetMHCII_allelesValid['DRB1_0438'] = 1;
NetMHCII_allelesValid['DRB1_0439'] = 1;
NetMHCII_allelesValid['DRB1_0440'] = 1;
NetMHCII_allelesValid['DRB1_0441'] = 1;
NetMHCII_allelesValid['DRB1_0442'] = 1;
NetMHCII_allelesValid['DRB1_0443'] = 1;
NetMHCII_allelesValid['DRB1_0444'] = 1;
NetMHCII_allelesValid['DRB1_0445'] = 1;
NetMHCII_allelesValid['DRB1_0446'] = 1;
NetMHCII_allelesValid['DRB1_0447'] = 1;
NetMHCII_allelesValid['DRB1_0448'] = 1;
NetMHCII_allelesValid['DRB1_0449'] = 1;
NetMHCII_allelesValid['DRB1_0450'] = 1;
NetMHCII_allelesValid['DRB1_0451'] = 1;
NetMHCII_allelesValid['DRB1_0452'] = 1;
NetMHCII_allelesValid['DRB1_0453'] = 1;
NetMHCII_allelesValid['DRB1_0454'] = 1;
NetMHCII_allelesValid['DRB1_0455'] = 1;
NetMHCII_allelesValid['DRB1_0456'] = 1;
NetMHCII_allelesValid['DRB1_0457'] = 1;
NetMHCII_allelesValid['DRB1_0458'] = 1;
NetMHCII_allelesValid['DRB1_0459'] = 1;
NetMHCII_allelesValid['DRB1_0460'] = 1;
NetMHCII_allelesValid['DRB1_0461'] = 1;
NetMHCII_allelesValid['DRB1_0462'] = 1;
NetMHCII_allelesValid['DRB1_0463'] = 1;
NetMHCII_allelesValid['DRB1_0464'] = 1;
NetMHCII_allelesValid['DRB1_0465'] = 1;
NetMHCII_allelesValid['DRB1_0466'] = 1;
NetMHCII_allelesValid['DRB1_0467'] = 1;
NetMHCII_allelesValid['DRB1_0468'] = 1;
NetMHCII_allelesValid['DRB1_0469'] = 1;
NetMHCII_allelesValid['DRB1_0470'] = 1;
NetMHCII_allelesValid['DRB1_0471'] = 1;
NetMHCII_allelesValid['DRB1_0472'] = 1;
NetMHCII_allelesValid['DRB1_0473'] = 1;
NetMHCII_allelesValid['DRB1_0474'] = 1;
NetMHCII_allelesValid['DRB1_0475'] = 1;
NetMHCII_allelesValid['DRB1_0476'] = 1;
NetMHCII_allelesValid['DRB1_0477'] = 1;
NetMHCII_allelesValid['DRB1_0478'] = 1;
NetMHCII_allelesValid['DRB1_0479'] = 1;
NetMHCII_allelesValid['DRB1_0480'] = 1;
NetMHCII_allelesValid['DRB1_0482'] = 1;
NetMHCII_allelesValid['DRB1_0483'] = 1;
NetMHCII_allelesValid['DRB1_0484'] = 1;
NetMHCII_allelesValid['DRB1_0485'] = 1;
NetMHCII_allelesValid['DRB1_0486'] = 1;
NetMHCII_allelesValid['DRB1_0487'] = 1;
NetMHCII_allelesValid['DRB1_0488'] = 1;
NetMHCII_allelesValid['DRB1_0489'] = 1;
NetMHCII_allelesValid['DRB1_0491'] = 1;
NetMHCII_allelesValid['DRB1_0701'] = 1;
NetMHCII_allelesValid['DRB1_0703'] = 1;
NetMHCII_allelesValid['DRB1_0704'] = 1;
NetMHCII_allelesValid['DRB1_0705'] = 1;
NetMHCII_allelesValid['DRB1_0706'] = 1;
NetMHCII_allelesValid['DRB1_0707'] = 1;
NetMHCII_allelesValid['DRB1_0708'] = 1;
NetMHCII_allelesValid['DRB1_0709'] = 1;
NetMHCII_allelesValid['DRB1_0711'] = 1;
NetMHCII_allelesValid['DRB1_0712'] = 1;
NetMHCII_allelesValid['DRB1_0713'] = 1;
NetMHCII_allelesValid['DRB1_0714'] = 1;
NetMHCII_allelesValid['DRB1_0715'] = 1;
NetMHCII_allelesValid['DRB1_0716'] = 1;
NetMHCII_allelesValid['DRB1_0717'] = 1;
NetMHCII_allelesValid['DRB1_0719'] = 1;
NetMHCII_allelesValid['DRB1_0801'] = 1;
NetMHCII_allelesValid['DRB1_0802'] = 1;
NetMHCII_allelesValid['DRB1_0803'] = 1;
NetMHCII_allelesValid['DRB1_0804'] = 1;
NetMHCII_allelesValid['DRB1_0805'] = 1;
NetMHCII_allelesValid['DRB1_0806'] = 1;
NetMHCII_allelesValid['DRB1_0807'] = 1;
NetMHCII_allelesValid['DRB1_0808'] = 1;
NetMHCII_allelesValid['DRB1_0809'] = 1;
NetMHCII_allelesValid['DRB1_0810'] = 1;
NetMHCII_allelesValid['DRB1_0811'] = 1;
NetMHCII_allelesValid['DRB1_0812'] = 1;
NetMHCII_allelesValid['DRB1_0813'] = 1;
NetMHCII_allelesValid['DRB1_0814'] = 1;
NetMHCII_allelesValid['DRB1_0815'] = 1;
NetMHCII_allelesValid['DRB1_0816'] = 1;
NetMHCII_allelesValid['DRB1_0818'] = 1;
NetMHCII_allelesValid['DRB1_0819'] = 1;
NetMHCII_allelesValid['DRB1_0820'] = 1;
NetMHCII_allelesValid['DRB1_0821'] = 1;
NetMHCII_allelesValid['DRB1_0822'] = 1;
NetMHCII_allelesValid['DRB1_0823'] = 1;
NetMHCII_allelesValid['DRB1_0824'] = 1;
NetMHCII_allelesValid['DRB1_0825'] = 1;
NetMHCII_allelesValid['DRB1_0826'] = 1;
NetMHCII_allelesValid['DRB1_0827'] = 1;
NetMHCII_allelesValid['DRB1_0828'] = 1;
NetMHCII_allelesValid['DRB1_0829'] = 1;
NetMHCII_allelesValid['DRB1_0830'] = 1;
NetMHCII_allelesValid['DRB1_0831'] = 1;
NetMHCII_allelesValid['DRB1_0832'] = 1;
NetMHCII_allelesValid['DRB1_0833'] = 1;
NetMHCII_allelesValid['DRB1_0834'] = 1;
NetMHCII_allelesValid['DRB1_0835'] = 1;
NetMHCII_allelesValid['DRB1_0836'] = 1;
NetMHCII_allelesValid['DRB1_0837'] = 1;
NetMHCII_allelesValid['DRB1_0838'] = 1;
NetMHCII_allelesValid['DRB1_0839'] = 1;
NetMHCII_allelesValid['DRB1_0840'] = 1;
NetMHCII_allelesValid['DRB1_0901'] = 1;
NetMHCII_allelesValid['DRB1_0902'] = 1;
NetMHCII_allelesValid['DRB1_0903'] = 1;
NetMHCII_allelesValid['DRB1_0904'] = 1;
NetMHCII_allelesValid['DRB1_0905'] = 1;
NetMHCII_allelesValid['DRB1_0906'] = 1;
NetMHCII_allelesValid['DRB1_0907'] = 1;
NetMHCII_allelesValid['DRB1_0908'] = 1;
NetMHCII_allelesValid['DRB1_0909'] = 1;
NetMHCII_allelesValid['DRB1_1001'] = 1;
NetMHCII_allelesValid['DRB1_1002'] = 1;
NetMHCII_allelesValid['DRB1_1003'] = 1;
NetMHCII_allelesValid['DRB1_1101'] = 1;
NetMHCII_allelesValid['DRB1_1102'] = 1;
NetMHCII_allelesValid['DRB1_1103'] = 1;
NetMHCII_allelesValid['DRB1_1104'] = 1;
NetMHCII_allelesValid['DRB1_1105'] = 1;
NetMHCII_allelesValid['DRB1_1106'] = 1;
NetMHCII_allelesValid['DRB1_1107'] = 1;
NetMHCII_allelesValid['DRB1_1108'] = 1;
NetMHCII_allelesValid['DRB1_1109'] = 1;
NetMHCII_allelesValid['DRB1_1110'] = 1;
NetMHCII_allelesValid['DRB1_1111'] = 1;
NetMHCII_allelesValid['DRB1_1112'] = 1;
NetMHCII_allelesValid['DRB1_1113'] = 1;
NetMHCII_allelesValid['DRB1_1114'] = 1;
NetMHCII_allelesValid['DRB1_1115'] = 1;
NetMHCII_allelesValid['DRB1_1116'] = 1;
NetMHCII_allelesValid['DRB1_1117'] = 1;
NetMHCII_allelesValid['DRB1_1118'] = 1;
NetMHCII_allelesValid['DRB1_1119'] = 1;
NetMHCII_allelesValid['DRB1_1120'] = 1;
NetMHCII_allelesValid['DRB1_1121'] = 1;
NetMHCII_allelesValid['DRB1_1124'] = 1;
NetMHCII_allelesValid['DRB1_1125'] = 1;
NetMHCII_allelesValid['DRB1_1127'] = 1;
NetMHCII_allelesValid['DRB1_1128'] = 1;
NetMHCII_allelesValid['DRB1_1129'] = 1;
NetMHCII_allelesValid['DRB1_1130'] = 1;
NetMHCII_allelesValid['DRB1_1131'] = 1;
NetMHCII_allelesValid['DRB1_1132'] = 1;
NetMHCII_allelesValid['DRB1_1133'] = 1;
NetMHCII_allelesValid['DRB1_1134'] = 1;
NetMHCII_allelesValid['DRB1_1135'] = 1;
NetMHCII_allelesValid['DRB1_1136'] = 1;
NetMHCII_allelesValid['DRB1_1137'] = 1;
NetMHCII_allelesValid['DRB1_1138'] = 1;
NetMHCII_allelesValid['DRB1_1139'] = 1;
NetMHCII_allelesValid['DRB1_1141'] = 1;
NetMHCII_allelesValid['DRB1_1142'] = 1;
NetMHCII_allelesValid['DRB1_1143'] = 1;
NetMHCII_allelesValid['DRB1_1144'] = 1;
NetMHCII_allelesValid['DRB1_1145'] = 1;
NetMHCII_allelesValid['DRB1_1146'] = 1;
NetMHCII_allelesValid['DRB1_1147'] = 1;
NetMHCII_allelesValid['DRB1_1148'] = 1;
NetMHCII_allelesValid['DRB1_1149'] = 1;
NetMHCII_allelesValid['DRB1_1150'] = 1;
NetMHCII_allelesValid['DRB1_1151'] = 1;
NetMHCII_allelesValid['DRB1_1152'] = 1;
NetMHCII_allelesValid['DRB1_1153'] = 1;
NetMHCII_allelesValid['DRB1_1154'] = 1;
NetMHCII_allelesValid['DRB1_1155'] = 1;
NetMHCII_allelesValid['DRB1_1156'] = 1;
NetMHCII_allelesValid['DRB1_1157'] = 1;
NetMHCII_allelesValid['DRB1_1158'] = 1;
NetMHCII_allelesValid['DRB1_1159'] = 1;
NetMHCII_allelesValid['DRB1_1160'] = 1;
NetMHCII_allelesValid['DRB1_1161'] = 1;
NetMHCII_allelesValid['DRB1_1162'] = 1;
NetMHCII_allelesValid['DRB1_1163'] = 1;
NetMHCII_allelesValid['DRB1_1164'] = 1;
NetMHCII_allelesValid['DRB1_1165'] = 1;
NetMHCII_allelesValid['DRB1_1166'] = 1;
NetMHCII_allelesValid['DRB1_1167'] = 1;
NetMHCII_allelesValid['DRB1_1168'] = 1;
NetMHCII_allelesValid['DRB1_1169'] = 1;
NetMHCII_allelesValid['DRB1_1170'] = 1;
NetMHCII_allelesValid['DRB1_1172'] = 1;
NetMHCII_allelesValid['DRB1_1173'] = 1;
NetMHCII_allelesValid['DRB1_1174'] = 1;
NetMHCII_allelesValid['DRB1_1175'] = 1;
NetMHCII_allelesValid['DRB1_1176'] = 1;
NetMHCII_allelesValid['DRB1_1177'] = 1;
NetMHCII_allelesValid['DRB1_1178'] = 1;
NetMHCII_allelesValid['DRB1_1179'] = 1;
NetMHCII_allelesValid['DRB1_1180'] = 1;
NetMHCII_allelesValid['DRB1_1181'] = 1;
NetMHCII_allelesValid['DRB1_1182'] = 1;
NetMHCII_allelesValid['DRB1_1183'] = 1;
NetMHCII_allelesValid['DRB1_1184'] = 1;
NetMHCII_allelesValid['DRB1_1185'] = 1;
NetMHCII_allelesValid['DRB1_1186'] = 1;
NetMHCII_allelesValid['DRB1_1187'] = 1;
NetMHCII_allelesValid['DRB1_1188'] = 1;
NetMHCII_allelesValid['DRB1_1189'] = 1;
NetMHCII_allelesValid['DRB1_1190'] = 1;
NetMHCII_allelesValid['DRB1_1191'] = 1;
NetMHCII_allelesValid['DRB1_1192'] = 1;
NetMHCII_allelesValid['DRB1_1193'] = 1;
NetMHCII_allelesValid['DRB1_1194'] = 1;
NetMHCII_allelesValid['DRB1_1195'] = 1;
NetMHCII_allelesValid['DRB1_1196'] = 1;
NetMHCII_allelesValid['DRB1_1201'] = 1;
NetMHCII_allelesValid['DRB1_1202'] = 1;
NetMHCII_allelesValid['DRB1_1203'] = 1;
NetMHCII_allelesValid['DRB1_1204'] = 1;
NetMHCII_allelesValid['DRB1_1205'] = 1;
NetMHCII_allelesValid['DRB1_1206'] = 1;
NetMHCII_allelesValid['DRB1_1207'] = 1;
NetMHCII_allelesValid['DRB1_1208'] = 1;
NetMHCII_allelesValid['DRB1_1209'] = 1;
NetMHCII_allelesValid['DRB1_1210'] = 1;
NetMHCII_allelesValid['DRB1_1211'] = 1;
NetMHCII_allelesValid['DRB1_1212'] = 1;
NetMHCII_allelesValid['DRB1_1213'] = 1;
NetMHCII_allelesValid['DRB1_1214'] = 1;
NetMHCII_allelesValid['DRB1_1215'] = 1;
NetMHCII_allelesValid['DRB1_1216'] = 1;
NetMHCII_allelesValid['DRB1_1217'] = 1;
NetMHCII_allelesValid['DRB1_1218'] = 1;
NetMHCII_allelesValid['DRB1_1219'] = 1;
NetMHCII_allelesValid['DRB1_1220'] = 1;
NetMHCII_allelesValid['DRB1_1221'] = 1;
NetMHCII_allelesValid['DRB1_1222'] = 1;
NetMHCII_allelesValid['DRB1_1223'] = 1;
NetMHCII_allelesValid['DRB1_1301'] = 1;
NetMHCII_allelesValid['DRB1_1302'] = 1;
NetMHCII_allelesValid['DRB1_1303'] = 1;
NetMHCII_allelesValid['DRB1_1304'] = 1;
NetMHCII_allelesValid['DRB1_1305'] = 1;
NetMHCII_allelesValid['DRB1_1306'] = 1;
NetMHCII_allelesValid['DRB1_1307'] = 1;
NetMHCII_allelesValid['DRB1_1308'] = 1;
NetMHCII_allelesValid['DRB1_1309'] = 1;
NetMHCII_allelesValid['DRB1_1310'] = 1;
NetMHCII_allelesValid['DRB1_13100'] = 1;
NetMHCII_allelesValid['DRB1_13101'] = 1;
NetMHCII_allelesValid['DRB1_1311'] = 1;
NetMHCII_allelesValid['DRB1_1312'] = 1;
NetMHCII_allelesValid['DRB1_1313'] = 1;
NetMHCII_allelesValid['DRB1_1314'] = 1;
NetMHCII_allelesValid['DRB1_1315'] = 1;
NetMHCII_allelesValid['DRB1_1316'] = 1;
NetMHCII_allelesValid['DRB1_1317'] = 1;
NetMHCII_allelesValid['DRB1_1318'] = 1;
NetMHCII_allelesValid['DRB1_1319'] = 1;
NetMHCII_allelesValid['DRB1_1320'] = 1;
NetMHCII_allelesValid['DRB1_1321'] = 1;
NetMHCII_allelesValid['DRB1_1322'] = 1;
NetMHCII_allelesValid['DRB1_1323'] = 1;
NetMHCII_allelesValid['DRB1_1324'] = 1;
NetMHCII_allelesValid['DRB1_1326'] = 1;
NetMHCII_allelesValid['DRB1_1327'] = 1;
NetMHCII_allelesValid['DRB1_1329'] = 1;
NetMHCII_allelesValid['DRB1_1330'] = 1;
NetMHCII_allelesValid['DRB1_1331'] = 1;
NetMHCII_allelesValid['DRB1_1332'] = 1;
NetMHCII_allelesValid['DRB1_1333'] = 1;
NetMHCII_allelesValid['DRB1_1334'] = 1;
NetMHCII_allelesValid['DRB1_1335'] = 1;
NetMHCII_allelesValid['DRB1_1336'] = 1;
NetMHCII_allelesValid['DRB1_1337'] = 1;
NetMHCII_allelesValid['DRB1_1338'] = 1;
NetMHCII_allelesValid['DRB1_1339'] = 1;
NetMHCII_allelesValid['DRB1_1341'] = 1;
NetMHCII_allelesValid['DRB1_1342'] = 1;
NetMHCII_allelesValid['DRB1_1343'] = 1;
NetMHCII_allelesValid['DRB1_1344'] = 1;
NetMHCII_allelesValid['DRB1_1346'] = 1;
NetMHCII_allelesValid['DRB1_1347'] = 1;
NetMHCII_allelesValid['DRB1_1348'] = 1;
NetMHCII_allelesValid['DRB1_1349'] = 1;
NetMHCII_allelesValid['DRB1_1350'] = 1;
NetMHCII_allelesValid['DRB1_1351'] = 1;
NetMHCII_allelesValid['DRB1_1352'] = 1;
NetMHCII_allelesValid['DRB1_1353'] = 1;
NetMHCII_allelesValid['DRB1_1354'] = 1;
NetMHCII_allelesValid['DRB1_1355'] = 1;
NetMHCII_allelesValid['DRB1_1356'] = 1;
NetMHCII_allelesValid['DRB1_1357'] = 1;
NetMHCII_allelesValid['DRB1_1358'] = 1;
NetMHCII_allelesValid['DRB1_1359'] = 1;
NetMHCII_allelesValid['DRB1_1360'] = 1;
NetMHCII_allelesValid['DRB1_1361'] = 1;
NetMHCII_allelesValid['DRB1_1362'] = 1;
NetMHCII_allelesValid['DRB1_1363'] = 1;
NetMHCII_allelesValid['DRB1_1364'] = 1;
NetMHCII_allelesValid['DRB1_1365'] = 1;
NetMHCII_allelesValid['DRB1_1366'] = 1;
NetMHCII_allelesValid['DRB1_1367'] = 1;
NetMHCII_allelesValid['DRB1_1368'] = 1;
NetMHCII_allelesValid['DRB1_1369'] = 1;
NetMHCII_allelesValid['DRB1_1370'] = 1;
NetMHCII_allelesValid['DRB1_1371'] = 1;
NetMHCII_allelesValid['DRB1_1372'] = 1;
NetMHCII_allelesValid['DRB1_1373'] = 1;
NetMHCII_allelesValid['DRB1_1374'] = 1;
NetMHCII_allelesValid['DRB1_1375'] = 1;
NetMHCII_allelesValid['DRB1_1376'] = 1;
NetMHCII_allelesValid['DRB1_1377'] = 1;
NetMHCII_allelesValid['DRB1_1378'] = 1;
NetMHCII_allelesValid['DRB1_1379'] = 1;
NetMHCII_allelesValid['DRB1_1380'] = 1;
NetMHCII_allelesValid['DRB1_1381'] = 1;
NetMHCII_allelesValid['DRB1_1382'] = 1;
NetMHCII_allelesValid['DRB1_1383'] = 1;
NetMHCII_allelesValid['DRB1_1384'] = 1;
NetMHCII_allelesValid['DRB1_1385'] = 1;
NetMHCII_allelesValid['DRB1_1386'] = 1;
NetMHCII_allelesValid['DRB1_1387'] = 1;
NetMHCII_allelesValid['DRB1_1388'] = 1;
NetMHCII_allelesValid['DRB1_1389'] = 1;
NetMHCII_allelesValid['DRB1_1390'] = 1;
NetMHCII_allelesValid['DRB1_1391'] = 1;
NetMHCII_allelesValid['DRB1_1392'] = 1;
NetMHCII_allelesValid['DRB1_1393'] = 1;
NetMHCII_allelesValid['DRB1_1394'] = 1;
NetMHCII_allelesValid['DRB1_1395'] = 1;
NetMHCII_allelesValid['DRB1_1396'] = 1;
NetMHCII_allelesValid['DRB1_1397'] = 1;
NetMHCII_allelesValid['DRB1_1398'] = 1;
NetMHCII_allelesValid['DRB1_1399'] = 1;
NetMHCII_allelesValid['DRB1_1401'] = 1;
NetMHCII_allelesValid['DRB1_1402'] = 1;
NetMHCII_allelesValid['DRB1_1403'] = 1;
NetMHCII_allelesValid['DRB1_1404'] = 1;
NetMHCII_allelesValid['DRB1_1405'] = 1;
NetMHCII_allelesValid['DRB1_1406'] = 1;
NetMHCII_allelesValid['DRB1_1407'] = 1;
NetMHCII_allelesValid['DRB1_1408'] = 1;
NetMHCII_allelesValid['DRB1_1409'] = 1;
NetMHCII_allelesValid['DRB1_1410'] = 1;
NetMHCII_allelesValid['DRB1_1411'] = 1;
NetMHCII_allelesValid['DRB1_1412'] = 1;
NetMHCII_allelesValid['DRB1_1413'] = 1;
NetMHCII_allelesValid['DRB1_1414'] = 1;
NetMHCII_allelesValid['DRB1_1415'] = 1;
NetMHCII_allelesValid['DRB1_1416'] = 1;
NetMHCII_allelesValid['DRB1_1417'] = 1;
NetMHCII_allelesValid['DRB1_1418'] = 1;
NetMHCII_allelesValid['DRB1_1419'] = 1;
NetMHCII_allelesValid['DRB1_1420'] = 1;
NetMHCII_allelesValid['DRB1_1421'] = 1;
NetMHCII_allelesValid['DRB1_1422'] = 1;
NetMHCII_allelesValid['DRB1_1423'] = 1;
NetMHCII_allelesValid['DRB1_1424'] = 1;
NetMHCII_allelesValid['DRB1_1425'] = 1;
NetMHCII_allelesValid['DRB1_1426'] = 1;
NetMHCII_allelesValid['DRB1_1427'] = 1;
NetMHCII_allelesValid['DRB1_1428'] = 1;
NetMHCII_allelesValid['DRB1_1429'] = 1;
NetMHCII_allelesValid['DRB1_1430'] = 1;
NetMHCII_allelesValid['DRB1_1431'] = 1;
NetMHCII_allelesValid['DRB1_1432'] = 1;
NetMHCII_allelesValid['DRB1_1433'] = 1;
NetMHCII_allelesValid['DRB1_1434'] = 1;
NetMHCII_allelesValid['DRB1_1435'] = 1;
NetMHCII_allelesValid['DRB1_1436'] = 1;
NetMHCII_allelesValid['DRB1_1437'] = 1;
NetMHCII_allelesValid['DRB1_1438'] = 1;
NetMHCII_allelesValid['DRB1_1439'] = 1;
NetMHCII_allelesValid['DRB1_1440'] = 1;
NetMHCII_allelesValid['DRB1_1441'] = 1;
NetMHCII_allelesValid['DRB1_1442'] = 1;
NetMHCII_allelesValid['DRB1_1443'] = 1;
NetMHCII_allelesValid['DRB1_1444'] = 1;
NetMHCII_allelesValid['DRB1_1445'] = 1;
NetMHCII_allelesValid['DRB1_1446'] = 1;
NetMHCII_allelesValid['DRB1_1447'] = 1;
NetMHCII_allelesValid['DRB1_1448'] = 1;
NetMHCII_allelesValid['DRB1_1449'] = 1;
NetMHCII_allelesValid['DRB1_1450'] = 1;
NetMHCII_allelesValid['DRB1_1451'] = 1;
NetMHCII_allelesValid['DRB1_1452'] = 1;
NetMHCII_allelesValid['DRB1_1453'] = 1;
NetMHCII_allelesValid['DRB1_1454'] = 1;
NetMHCII_allelesValid['DRB1_1455'] = 1;
NetMHCII_allelesValid['DRB1_1456'] = 1;
NetMHCII_allelesValid['DRB1_1457'] = 1;
NetMHCII_allelesValid['DRB1_1458'] = 1;
NetMHCII_allelesValid['DRB1_1459'] = 1;
NetMHCII_allelesValid['DRB1_1460'] = 1;
NetMHCII_allelesValid['DRB1_1461'] = 1;
NetMHCII_allelesValid['DRB1_1462'] = 1;
NetMHCII_allelesValid['DRB1_1463'] = 1;
NetMHCII_allelesValid['DRB1_1464'] = 1;
NetMHCII_allelesValid['DRB1_1465'] = 1;
NetMHCII_allelesValid['DRB1_1467'] = 1;
NetMHCII_allelesValid['DRB1_1468'] = 1;
NetMHCII_allelesValid['DRB1_1469'] = 1;
NetMHCII_allelesValid['DRB1_1470'] = 1;
NetMHCII_allelesValid['DRB1_1471'] = 1;
NetMHCII_allelesValid['DRB1_1472'] = 1;
NetMHCII_allelesValid['DRB1_1473'] = 1;
NetMHCII_allelesValid['DRB1_1474'] = 1;
NetMHCII_allelesValid['DRB1_1475'] = 1;
NetMHCII_allelesValid['DRB1_1476'] = 1;
NetMHCII_allelesValid['DRB1_1477'] = 1;
NetMHCII_allelesValid['DRB1_1478'] = 1;
NetMHCII_allelesValid['DRB1_1479'] = 1;
NetMHCII_allelesValid['DRB1_1480'] = 1;
NetMHCII_allelesValid['DRB1_1481'] = 1;
NetMHCII_allelesValid['DRB1_1482'] = 1;
NetMHCII_allelesValid['DRB1_1483'] = 1;
NetMHCII_allelesValid['DRB1_1484'] = 1;
NetMHCII_allelesValid['DRB1_1485'] = 1;
NetMHCII_allelesValid['DRB1_1486'] = 1;
NetMHCII_allelesValid['DRB1_1487'] = 1;
NetMHCII_allelesValid['DRB1_1488'] = 1;
NetMHCII_allelesValid['DRB1_1489'] = 1;
NetMHCII_allelesValid['DRB1_1490'] = 1;
NetMHCII_allelesValid['DRB1_1491'] = 1;
NetMHCII_allelesValid['DRB1_1493'] = 1;
NetMHCII_allelesValid['DRB1_1494'] = 1;
NetMHCII_allelesValid['DRB1_1495'] = 1;
NetMHCII_allelesValid['DRB1_1496'] = 1;
NetMHCII_allelesValid['DRB1_1497'] = 1;
NetMHCII_allelesValid['DRB1_1498'] = 1;
NetMHCII_allelesValid['DRB1_1499'] = 1;
NetMHCII_allelesValid['DRB1_1501'] = 1;
NetMHCII_allelesValid['DRB1_1502'] = 1;
NetMHCII_allelesValid['DRB1_1503'] = 1;
NetMHCII_allelesValid['DRB1_1504'] = 1;
NetMHCII_allelesValid['DRB1_1505'] = 1;
NetMHCII_allelesValid['DRB1_1506'] = 1;
NetMHCII_allelesValid['DRB1_1507'] = 1;
NetMHCII_allelesValid['DRB1_1508'] = 1;
NetMHCII_allelesValid['DRB1_1509'] = 1;
NetMHCII_allelesValid['DRB1_1510'] = 1;
NetMHCII_allelesValid['DRB1_1511'] = 1;
NetMHCII_allelesValid['DRB1_1512'] = 1;
NetMHCII_allelesValid['DRB1_1513'] = 1;
NetMHCII_allelesValid['DRB1_1514'] = 1;
NetMHCII_allelesValid['DRB1_1515'] = 1;
NetMHCII_allelesValid['DRB1_1516'] = 1;
NetMHCII_allelesValid['DRB1_1518'] = 1;
NetMHCII_allelesValid['DRB1_1519'] = 1;
NetMHCII_allelesValid['DRB1_1520'] = 1;
NetMHCII_allelesValid['DRB1_1521'] = 1;
NetMHCII_allelesValid['DRB1_1522'] = 1;
NetMHCII_allelesValid['DRB1_1523'] = 1;
NetMHCII_allelesValid['DRB1_1524'] = 1;
NetMHCII_allelesValid['DRB1_1525'] = 1;
NetMHCII_allelesValid['DRB1_1526'] = 1;
NetMHCII_allelesValid['DRB1_1527'] = 1;
NetMHCII_allelesValid['DRB1_1528'] = 1;
NetMHCII_allelesValid['DRB1_1529'] = 1;
NetMHCII_allelesValid['DRB1_1530'] = 1;
NetMHCII_allelesValid['DRB1_1531'] = 1;
NetMHCII_allelesValid['DRB1_1532'] = 1;
NetMHCII_allelesValid['DRB1_1533'] = 1;
NetMHCII_allelesValid['DRB1_1534'] = 1;
NetMHCII_allelesValid['DRB1_1535'] = 1;
NetMHCII_allelesValid['DRB1_1536'] = 1;
NetMHCII_allelesValid['DRB1_1537'] = 1;
NetMHCII_allelesValid['DRB1_1538'] = 1;
NetMHCII_allelesValid['DRB1_1539'] = 1;
NetMHCII_allelesValid['DRB1_1540'] = 1;
NetMHCII_allelesValid['DRB1_1541'] = 1;
NetMHCII_allelesValid['DRB1_1542'] = 1;
NetMHCII_allelesValid['DRB1_1543'] = 1;
NetMHCII_allelesValid['DRB1_1544'] = 1;
NetMHCII_allelesValid['DRB1_1545'] = 1;
NetMHCII_allelesValid['DRB1_1546'] = 1;
NetMHCII_allelesValid['DRB1_1547'] = 1;
NetMHCII_allelesValid['DRB1_1548'] = 1;
NetMHCII_allelesValid['DRB1_1549'] = 1;
NetMHCII_allelesValid['DRB1_1601'] = 1;
NetMHCII_allelesValid['DRB1_1602'] = 1;
NetMHCII_allelesValid['DRB1_1603'] = 1;
NetMHCII_allelesValid['DRB1_1604'] = 1;
NetMHCII_allelesValid['DRB1_1605'] = 1;
NetMHCII_allelesValid['DRB1_1607'] = 1;
NetMHCII_allelesValid['DRB1_1608'] = 1;
NetMHCII_allelesValid['DRB1_1609'] = 1;
NetMHCII_allelesValid['DRB1_1610'] = 1;
NetMHCII_allelesValid['DRB1_1611'] = 1;
NetMHCII_allelesValid['DRB1_1612'] = 1;
NetMHCII_allelesValid['DRB1_1614'] = 1;
NetMHCII_allelesValid['DRB1_1615'] = 1;
NetMHCII_allelesValid['DRB1_1616'] = 1;
NetMHCII_allelesValid['DRB3_0101'] = 1;
NetMHCII_allelesValid['DRB3_0104'] = 1;
NetMHCII_allelesValid['DRB3_0105'] = 1;
NetMHCII_allelesValid['DRB3_0108'] = 1;
NetMHCII_allelesValid['DRB3_0109'] = 1;
NetMHCII_allelesValid['DRB3_0111'] = 1;
NetMHCII_allelesValid['DRB3_0112'] = 1;
NetMHCII_allelesValid['DRB3_0113'] = 1;
NetMHCII_allelesValid['DRB3_0114'] = 1;
NetMHCII_allelesValid['DRB3_0201'] = 1;
NetMHCII_allelesValid['DRB3_0202'] = 1;
NetMHCII_allelesValid['DRB3_0204'] = 1;
NetMHCII_allelesValid['DRB3_0205'] = 1;
NetMHCII_allelesValid['DRB3_0209'] = 1;
NetMHCII_allelesValid['DRB3_0210'] = 1;
NetMHCII_allelesValid['DRB3_0211'] = 1;
NetMHCII_allelesValid['DRB3_0212'] = 1;
NetMHCII_allelesValid['DRB3_0213'] = 1;
NetMHCII_allelesValid['DRB3_0214'] = 1;
NetMHCII_allelesValid['DRB3_0215'] = 1;
NetMHCII_allelesValid['DRB3_0216'] = 1;
NetMHCII_allelesValid['DRB3_0217'] = 1;
NetMHCII_allelesValid['DRB3_0218'] = 1;
NetMHCII_allelesValid['DRB3_0219'] = 1;
NetMHCII_allelesValid['DRB3_0220'] = 1;
NetMHCII_allelesValid['DRB3_0221'] = 1;
NetMHCII_allelesValid['DRB3_0222'] = 1;
NetMHCII_allelesValid['DRB3_0223'] = 1;
NetMHCII_allelesValid['DRB3_0224'] = 1;
NetMHCII_allelesValid['DRB3_0225'] = 1;
NetMHCII_allelesValid['DRB3_0301'] = 1;
NetMHCII_allelesValid['DRB3_0303'] = 1;
NetMHCII_allelesValid['DRB4_0101'] = 1;
NetMHCII_allelesValid['DRB4_0103'] = 1;
NetMHCII_allelesValid['DRB5_0101'] = 1;
NetMHCII_allelesValid['DRB5_0102'] = 1;
NetMHCII_allelesValid['DRB5_0103'] = 1;
NetMHCII_allelesValid['DRB5_0104'] = 1;
NetMHCII_allelesValid['DRB5_0105'] = 1;
NetMHCII_allelesValid['DRB5_0106'] = 1;
NetMHCII_allelesValid['DRB5_0108N'] = 1;
NetMHCII_allelesValid['DRB5_0111'] = 1;
NetMHCII_allelesValid['DRB5_0112'] = 1;
NetMHCII_allelesValid['DRB5_0113'] = 1;
NetMHCII_allelesValid['DRB5_0114'] = 1;
NetMHCII_allelesValid['DRB5_0202'] = 1;
NetMHCII_allelesValid['DRB5_0203'] = 1;
NetMHCII_allelesValid['DRB5_0204'] = 1;
NetMHCII_allelesValid['DRB5_0205'] = 1;
NetMHCII_allelesValid['H-2-IAb'] = 1;
NetMHCII_allelesValid['H-2-IAd'] = 1;

</script>


<br>

<div id='$ids[slave0]' style='display:block'>

<b>Seleccionar especies / loci </b> 
<br>
<select name='$names[master]' size='1' onchange=\"NetMHCII_showselect(this,'NetMHCII_slave_B', 'NetMHCII_slave_A');NetMHCII_showfullalpha(this.value);\">
<option value='1'>DP</option>
<option value='2'>DQ</option>
<option value='3'>DRB1</option>
<option value='4'>DRB3</option>
<option value='5'>DRB4</option>
<option value='6'>DRB5</option>
<option value='7'>Mouse (H-2)</option>
</select>
<br>
<br><b>Seleccione alelo (m&aacutex. 20 por presentaci&oacuten) </b><br>
<span id='$ids[slave_A0]' style='display:inline'>
<select id='$ids[slaveADP]' name='$names[slaveA]' size='12' onclick=\"NetMHCII_addAlleleAB('DP');\">
<option value='DPA10103'>DPA1*0103</option>
<option value='DPA10104'>DPA1*0104</option>
<option value='DPA10105'>DPA1*0105</option>
<option value='DPA10106'>DPA1*0106</option>
<option value='DPA10107'>DPA1*0107</option>
<option value='DPA10108'>DPA1*0108</option>
<option value='DPA10109'>DPA1*0109</option>
<option value='DPA10110'>DPA1*0110</option>
<option value='DPA10201'>DPA1*0201</option>
<option value='DPA10202'>DPA1*0202</option>
<option value='DPA10203'>DPA1*0203</option>
<option value='DPA10204'>DPA1*0204</option>
<option value='DPA10301'>DPA1*0301</option>
<option value='DPA10302'>DPA1*0302</option>
<option value='DPA10303'>DPA1*0303</option>
<option value='DPA10401'>DPA1*0401</option>
</select>
</span>
<span id='$ids[slave_B0]' style='display:inline'>
<select id='$ids[slaveBDP]' name='$names[slaveB]' size='12' onclick=\"NetMHCII_addAlleleAB('DP');\">
<option value='DPB10101'>DPB1*0101</option>
<option value='DPB10201'>DPB1*0201</option>
<option value='DPB10202'>DPB1*0202</option>
<option value='DPB10301'>DPB1*0301</option>
<option value='DPB10401'>DPB1*0401</option>
<option value='DPB10402'>DPB1*0402</option>
<option value='DPB10501'>DPB1*0501</option>
<option value='DPB10601'>DPB1*0601</option>
<option value='DPB10801'>DPB1*0801</option>
<option value='DPB110001'>DPB1*10001</option>
<option value='DPB11001'>DPB1*1001</option>
<option value='DPB110101'>DPB1*10101</option>
<option value='DPB110201'>DPB1*10201</option>
<option value='DPB110301'>DPB1*10301</option>
<option value='DPB110401'>DPB1*10401</option>
<option value='DPB110501'>DPB1*10501</option>
<option value='DPB110601'>DPB1*10601</option>
<option value='DPB110701'>DPB1*10701</option>
<option value='DPB110801'>DPB1*10801</option>
<option value='DPB110901'>DPB1*10901</option>
<option value='DPB111001'>DPB1*11001</option>
<option value='DPB11101'>DPB1*1101</option>
<option value='DPB111101'>DPB1*11101</option>
<option value='DPB111201'>DPB1*11201</option>
<option value='DPB111301'>DPB1*11301</option>
<option value='DPB111401'>DPB1*11401</option>
<option value='DPB111501'>DPB1*11501</option>
<option value='DPB111601'>DPB1*11601</option>
<option value='DPB111701'>DPB1*11701</option>
<option value='DPB111801'>DPB1*11801</option>
<option value='DPB111901'>DPB1*11901</option>
<option value='DPB112101'>DPB1*12101</option>
<option value='DPB112201'>DPB1*12201</option>
<option value='DPB112301'>DPB1*12301</option>
<option value='DPB112401'>DPB1*12401</option>
<option value='DPB112501'>DPB1*12501</option>
<option value='DPB112601'>DPB1*12601</option>
<option value='DPB112701'>DPB1*12701</option>
<option value='DPB112801'>DPB1*12801</option>
<option value='DPB112901'>DPB1*12901</option>
<option value='DPB113001'>DPB1*13001</option>
<option value='DPB11301'>DPB1*1301</option>
<option value='DPB113101'>DPB1*13101</option>
<option value='DPB113201'>DPB1*13201</option>
<option value='DPB113301'>DPB1*13301</option>
<option value='DPB113401'>DPB1*13401</option>
<option value='DPB11401'>DPB1*1401</option>
<option value='DPB11501'>DPB1*1501</option>
<option value='DPB11601'>DPB1*1601</option>
<option value='DPB11701'>DPB1*1701</option>
<option value='DPB11801'>DPB1*1801</option>
<option value='DPB11901'>DPB1*1901</option>
<option value='DPB12001'>DPB1*2001</option>
<option value='DPB12101'>DPB1*2101</option>
<option value='DPB12201'>DPB1*2201</option>
<option value='DPB12301'>DPB1*2301</option>
<option value='DPB12401'>DPB1*2401</option>
<option value='DPB12501'>DPB1*2501</option>
<option value='DPB12601'>DPB1*2601</option>
<option value='DPB12701'>DPB1*2701</option>
<option value='DPB12801'>DPB1*2801</option>
<option value='DPB12901'>DPB1*2901</option>
<option value='DPB13001'>DPB1*3001</option>
<option value='DPB13101'>DPB1*3101</option>
<option value='DPB13201'>DPB1*3201</option>
<option value='DPB13301'>DPB1*3301</option>
<option value='DPB13401'>DPB1*3401</option>
<option value='DPB13501'>DPB1*3501</option>
<option value='DPB13601'>DPB1*3601</option>
<option value='DPB13701'>DPB1*3701</option>
<option value='DPB13801'>DPB1*3801</option>
<option value='DPB13901'>DPB1*3901</option>
<option value='DPB14001'>DPB1*4001</option>
<option value='DPB14101'>DPB1*4101</option>
<option value='DPB14401'>DPB1*4401</option>
<option value='DPB14501'>DPB1*4501</option>
<option value='DPB14601'>DPB1*4601</option>
<option value='DPB14701'>DPB1*4701</option>
<option value='DPB14801'>DPB1*4801</option>
<option value='DPB14901'>DPB1*4901</option>
<option value='DPB15001'>DPB1*5001</option>
<option value='DPB15101'>DPB1*5101</option>
<option value='DPB15201'>DPB1*5201</option>
<option value='DPB15301'>DPB1*5301</option>
<option value='DPB15401'>DPB1*5401</option>
<option value='DPB15501'>DPB1*5501</option>
<option value='DPB15601'>DPB1*5601</option>
<option value='DPB15801'>DPB1*5801</option>
<option value='DPB15901'>DPB1*5901</option>
<option value='DPB16001'>DPB1*6001</option>
<option value='DPB16201'>DPB1*6201</option>
<option value='DPB16301'>DPB1*6301</option>
<option value='DPB16501'>DPB1*6501</option>
<option value='DPB16601'>DPB1*6601</option>
<option value='DPB16701'>DPB1*6701</option>
<option value='DPB16801'>DPB1*6801</option>
<option value='DPB16901'>DPB1*6901</option>
<option value='DPB17001'>DPB1*7001</option>
<option value='DPB17101'>DPB1*7101</option>
<option value='DPB17201'>DPB1*7201</option>
<option value='DPB17301'>DPB1*7301</option>
<option value='DPB17401'>DPB1*7401</option>
<option value='DPB17501'>DPB1*7501</option>
<option value='DPB17601'>DPB1*7601</option>
<option value='DPB17701'>DPB1*7701</option>
<option value='DPB17801'>DPB1*7801</option>
<option value='DPB17901'>DPB1*7901</option>
<option value='DPB18001'>DPB1*8001</option>
<option value='DPB18101'>DPB1*8101</option>
<option value='DPB18201'>DPB1*8201</option>
<option value='DPB18301'>DPB1*8301</option>
<option value='DPB18401'>DPB1*8401</option>
<option value='DPB18501'>DPB1*8501</option>
<option value='DPB18601'>DPB1*8601</option>
<option value='DPB18701'>DPB1*8701</option>
<option value='DPB18801'>DPB1*8801</option>
<option value='DPB18901'>DPB1*8901</option>
<option value='DPB19001'>DPB1*9001</option>
<option value='DPB19101'>DPB1*9101</option>
<option value='DPB19201'>DPB1*9201</option>
<option value='DPB19301'>DPB1*9301</option>
<option value='DPB19401'>DPB1*9401</option>
<option value='DPB19501'>DPB1*9501</option>
<option value='DPB19601'>DPB1*9601</option>
<option value='DPB19701'>DPB1*9701</option>
<option value='DPB19801'>DPB1*9801</option>
<option value='DPB19901'>DPB1*9901</option>
<option value='DPB10901'>DPB1*0901</option>
</select>
</span>
<span id='$ids[slave_A1]' style='display:none'>
<select id='$ids[slaveADQ]' name='$names[slaveA]' size='12' onclick=\"NetMHCII_addAlleleAB('DQ');\">
<option value='DQA10101'>DQA1*0101</option>
<option value='DQA10102'>DQA1*0102</option>
<option value='DQA10103'>DQA1*0103</option>
<option value='DQA10104'>DQA1*0104</option>
<option value='DQA10105'>DQA1*0105</option>
<option value='DQA10106'>DQA1*0106</option>
<option value='DQA10107'>DQA1*0107</option>
<option value='DQA10108'>DQA1*0108</option>
<option value='DQA10109'>DQA1*0109</option>
<option value='DQA10201'>DQA1*0201</option>
<option value='DQA10301'>DQA1*0301</option>
<option value='DQA10302'>DQA1*0302</option>
<option value='DQA10303'>DQA1*0303</option>
<option value='DQA10401'>DQA1*0401</option>
<option value='DQA10402'>DQA1*0402</option>
<option value='DQA10404'>DQA1*0404</option>
<option value='DQA10501'>DQA1*0501</option>
<option value='DQA10503'>DQA1*0503</option>
<option value='DQA10504'>DQA1*0504</option>
<option value='DQA10505'>DQA1*0505</option>
<option value='DQA10506'>DQA1*0506</option>
<option value='DQA10507'>DQA1*0507</option>
<option value='DQA10508'>DQA1*0508</option>
<option value='DQA10509'>DQA1*0509</option>
<option value='DQA10510'>DQA1*0510</option>
<option value='DQA10511'>DQA1*0511</option>
<option value='DQA10601'>DQA1*0601</option>
<option value='DQA10602'>DQA1*0602</option>
</select>
</span>
<span id='$ids[slave_B1]' style='display:none'>
<select id='$ids[slaveBDQ]' name='$names[slaveB]' size='12' onclick=\"NetMHCII_addAlleleAB('DQ');\">
<option value='DQB10201'>DQB1*0201</option>
<option value='DQB10202'>DQB1*0202</option>
<option value='DQB10203'>DQB1*0203</option>
<option value='DQB10204'>DQB1*0204</option>
<option value='DQB10205'>DQB1*0205</option>
<option value='DQB10206'>DQB1*0206</option>
<option value='DQB10301'>DQB1*0301</option>
<option value='DQB10302'>DQB1*0302</option>
<option value='DQB10303'>DQB1*0303</option>
<option value='DQB10304'>DQB1*0304</option>
<option value='DQB10305'>DQB1*0305</option>
<option value='DQB10306'>DQB1*0306</option>
<option value='DQB10307'>DQB1*0307</option>
<option value='DQB10308'>DQB1*0308</option>
<option value='DQB10309'>DQB1*0309</option>
<option value='DQB10310'>DQB1*0310</option>
<option value='DQB10311'>DQB1*0311</option>
<option value='DQB10312'>DQB1*0312</option>
<option value='DQB10313'>DQB1*0313</option>
<option value='DQB10314'>DQB1*0314</option>
<option value='DQB10315'>DQB1*0315</option>
<option value='DQB10316'>DQB1*0316</option>
<option value='DQB10317'>DQB1*0317</option>
<option value='DQB10318'>DQB1*0318</option>
<option value='DQB10319'>DQB1*0319</option>
<option value='DQB10320'>DQB1*0320</option>
<option value='DQB10321'>DQB1*0321</option>
<option value='DQB10322'>DQB1*0322</option>
<option value='DQB10323'>DQB1*0323</option>
<option value='DQB10324'>DQB1*0324</option>
<option value='DQB10325'>DQB1*0325</option>
<option value='DQB10326'>DQB1*0326</option>
<option value='DQB10327'>DQB1*0327</option>
<option value='DQB10328'>DQB1*0328</option>
<option value='DQB10329'>DQB1*0329</option>
<option value='DQB10330'>DQB1*0330</option>
<option value='DQB10331'>DQB1*0331</option>
<option value='DQB10332'>DQB1*0332</option>
<option value='DQB10333'>DQB1*0333</option>
<option value='DQB10334'>DQB1*0334</option>
<option value='DQB10335'>DQB1*0335</option>
<option value='DQB10336'>DQB1*0336</option>
<option value='DQB10337'>DQB1*0337</option>
<option value='DQB10338'>DQB1*0338</option>
<option value='DQB10401'>DQB1*0401</option>
<option value='DQB10402'>DQB1*0402</option>
<option value='DQB10403'>DQB1*0403</option>
<option value='DQB10404'>DQB1*0404</option>
<option value='DQB10405'>DQB1*0405</option>
<option value='DQB10406'>DQB1*0406</option>
<option value='DQB10407'>DQB1*0407</option>
<option value='DQB10408'>DQB1*0408</option>
<option value='DQB10501'>DQB1*0501</option>
<option value='DQB10502'>DQB1*0502</option>
<option value='DQB10503'>DQB1*0503</option>
<option value='DQB10505'>DQB1*0505</option>
<option value='DQB10506'>DQB1*0506</option>
<option value='DQB10507'>DQB1*0507</option>
<option value='DQB10508'>DQB1*0508</option>
<option value='DQB10509'>DQB1*0509</option>
<option value='DQB10510'>DQB1*0510</option>
<option value='DQB10511'>DQB1*0511</option>
<option value='DQB10512'>DQB1*0512</option>
<option value='DQB10513'>DQB1*0513</option>
<option value='DQB10514'>DQB1*0514</option>
<option value='DQB10601'>DQB1*0601</option>
<option value='DQB10602'>DQB1*0602</option>
<option value='DQB10603'>DQB1*0603</option>
<option value='DQB10604'>DQB1*0604</option>
<option value='DQB10607'>DQB1*0607</option>
<option value='DQB10608'>DQB1*0608</option>
<option value='DQB10609'>DQB1*0609</option>
<option value='DQB10610'>DQB1*0610</option>
<option value='DQB10611'>DQB1*0611</option>
<option value='DQB10612'>DQB1*0612</option>
<option value='DQB10614'>DQB1*0614</option>
<option value='DQB10615'>DQB1*0615</option>
<option value='DQB10616'>DQB1*0616</option>
<option value='DQB10617'>DQB1*0617</option>
<option value='DQB10618'>DQB1*0618</option>
<option value='DQB10619'>DQB1*0619</option>
<option value='DQB10621'>DQB1*0621</option>
<option value='DQB10622'>DQB1*0622</option>
<option value='DQB10623'>DQB1*0623</option>
<option value='DQB10624'>DQB1*0624</option>
<option value='DQB10625'>DQB1*0625</option>
<option value='DQB10627'>DQB1*0627</option>
<option value='DQB10628'>DQB1*0628</option>
<option value='DQB10629'>DQB1*0629</option>
<option value='DQB10630'>DQB1*0630</option>
<option value='DQB10631'>DQB1*0631</option>
<option value='DQB10632'>DQB1*0632</option>
<option value='DQB10633'>DQB1*0633</option>
<option value='DQB10634'>DQB1*0634</option>
<option value='DQB10635'>DQB1*0635</option>
<option value='DQB10636'>DQB1*0636</option>
<option value='DQB10637'>DQB1*0637</option>
<option value='DQB10638'>DQB1*0638</option>
<option value='DQB10639'>DQB1*0639</option>
<option value='DQB10640'>DQB1*0640</option>
<option value='DQB10641'>DQB1*0641</option>
<option value='DQB10642'>DQB1*0642</option>
<option value='DQB10643'>DQB1*0643</option>
<option value='DQB10644'>DQB1*0644</option>
</select>
</span>
<span id='$ids[slave_B2]' style='display:none'>
<select name='$names[slaveB]' size='12' multiple='' onclick='NetMHCII_addAlleleB(this)'>
<option value='DRB1_0101'>DRB1*0101</option>
<option value='DRB1_0102'>DRB1*0102</option>
<option value='DRB1_0103'>DRB1*0103</option>
<option value='DRB1_0104'>DRB1*0104</option>
<option value='DRB1_0105'>DRB1*0105</option>
<option value='DRB1_0106'>DRB1*0106</option>
<option value='DRB1_0107'>DRB1*0107</option>
<option value='DRB1_0108'>DRB1*0108</option>
<option value='DRB1_0109'>DRB1*0109</option>
<option value='DRB1_0110'>DRB1*0110</option>
<option value='DRB1_0111'>DRB1*0111</option>
<option value='DRB1_0112'>DRB1*0112</option>
<option value='DRB1_0113'>DRB1*0113</option>
<option value='DRB1_0114'>DRB1*0114</option>
<option value='DRB1_0115'>DRB1*0115</option>
<option value='DRB1_0116'>DRB1*0116</option>
<option value='DRB1_0117'>DRB1*0117</option>
<option value='DRB1_0118'>DRB1*0118</option>
<option value='DRB1_0119'>DRB1*0119</option>
<option value='DRB1_0120'>DRB1*0120</option>
<option value='DRB1_0121'>DRB1*0121</option>
<option value='DRB1_0122'>DRB1*0122</option>
<option value='DRB1_0123'>DRB1*0123</option>
<option value='DRB1_0124'>DRB1*0124</option>
<option value='DRB1_0125'>DRB1*0125</option>
<option value='DRB1_0126'>DRB1*0126</option>
<option value='DRB1_0127'>DRB1*0127</option>
<option value='DRB1_0128'>DRB1*0128</option>
<option value='DRB1_0129'>DRB1*0129</option>
<option value='DRB1_0130'>DRB1*0130</option>
<option value='DRB1_0131'>DRB1*0131</option>
<option value='DRB1_0132'>DRB1*0132</option>
<option value='DRB1_0301'>DRB1*0301</option>
<option value='DRB1_0302'>DRB1*0302</option>
<option value='DRB1_0303'>DRB1*0303</option>
<option value='DRB1_0304'>DRB1*0304</option>
<option value='DRB1_0305'>DRB1*0305</option>
<option value='DRB1_0306'>DRB1*0306</option>
<option value='DRB1_0307'>DRB1*0307</option>
<option value='DRB1_0308'>DRB1*0308</option>
<option value='DRB1_0310'>DRB1*0310</option>
<option value='DRB1_0311'>DRB1*0311</option>
<option value='DRB1_0313'>DRB1*0313</option>
<option value='DRB1_0314'>DRB1*0314</option>
<option value='DRB1_0315'>DRB1*0315</option>
<option value='DRB1_0317'>DRB1*0317</option>
<option value='DRB1_0318'>DRB1*0318</option>
<option value='DRB1_0319'>DRB1*0319</option>
<option value='DRB1_0320'>DRB1*0320</option>
<option value='DRB1_0321'>DRB1*0321</option>
<option value='DRB1_0322'>DRB1*0322</option>
<option value='DRB1_0323'>DRB1*0323</option>
<option value='DRB1_0324'>DRB1*0324</option>
<option value='DRB1_0325'>DRB1*0325</option>
<option value='DRB1_0326'>DRB1*0326</option>
<option value='DRB1_0327'>DRB1*0327</option>
<option value='DRB1_0328'>DRB1*0328</option>
<option value='DRB1_0329'>DRB1*0329</option>
<option value='DRB1_0330'>DRB1*0330</option>
<option value='DRB1_0331'>DRB1*0331</option>
<option value='DRB1_0332'>DRB1*0332</option>
<option value='DRB1_0333'>DRB1*0333</option>
<option value='DRB1_0334'>DRB1*0334</option>
<option value='DRB1_0335'>DRB1*0335</option>
<option value='DRB1_0336'>DRB1*0336</option>
<option value='DRB1_0337'>DRB1*0337</option>
<option value='DRB1_0338'>DRB1*0338</option>
<option value='DRB1_0339'>DRB1*0339</option>
<option value='DRB1_0340'>DRB1*0340</option>
<option value='DRB1_0341'>DRB1*0341</option>
<option value='DRB1_0342'>DRB1*0342</option>
<option value='DRB1_0343'>DRB1*0343</option>
<option value='DRB1_0344'>DRB1*0344</option>
<option value='DRB1_0345'>DRB1*0345</option>
<option value='DRB1_0346'>DRB1*0346</option>
<option value='DRB1_0347'>DRB1*0347</option>
<option value='DRB1_0348'>DRB1*0348</option>
<option value='DRB1_0349'>DRB1*0349</option>
<option value='DRB1_0350'>DRB1*0350</option>
<option value='DRB1_0351'>DRB1*0351</option>
<option value='DRB1_0352'>DRB1*0352</option>
<option value='DRB1_0353'>DRB1*0353</option>
<option value='DRB1_0354'>DRB1*0354</option>
<option value='DRB1_0355'>DRB1*0355</option>
<option value='DRB1_0401'>DRB1*0401</option>
<option value='DRB1_0402'>DRB1*0402</option>
<option value='DRB1_0403'>DRB1*0403</option>
<option value='DRB1_0404'>DRB1*0404</option>
<option value='DRB1_0405'>DRB1*0405</option>
<option value='DRB1_0406'>DRB1*0406</option>
<option value='DRB1_0407'>DRB1*0407</option>
<option value='DRB1_0408'>DRB1*0408</option>
<option value='DRB1_0409'>DRB1*0409</option>
<option value='DRB1_0410'>DRB1*0410</option>
<option value='DRB1_0411'>DRB1*0411</option>
<option value='DRB1_0412'>DRB1*0412</option>
<option value='DRB1_0413'>DRB1*0413</option>
<option value='DRB1_0414'>DRB1*0414</option>
<option value='DRB1_0415'>DRB1*0415</option>
<option value='DRB1_0416'>DRB1*0416</option>
<option value='DRB1_0417'>DRB1*0417</option>
<option value='DRB1_0418'>DRB1*0418</option>
<option value='DRB1_0419'>DRB1*0419</option>
<option value='DRB1_0423'>DRB1*0423</option>
<option value='DRB1_0424'>DRB1*0424</option>
<option value='DRB1_0426'>DRB1*0426</option>
<option value='DRB1_0427'>DRB1*0427</option>
<option value='DRB1_0428'>DRB1*0428</option>
<option value='DRB1_0429'>DRB1*0429</option>
<option value='DRB1_0430'>DRB1*0430</option>
<option value='DRB1_0431'>DRB1*0431</option>
<option value='DRB1_0433'>DRB1*0433</option>
<option value='DRB1_0434'>DRB1*0434</option>
<option value='DRB1_0435'>DRB1*0435</option>
<option value='DRB1_0436'>DRB1*0436</option>
<option value='DRB1_0437'>DRB1*0437</option>
<option value='DRB1_0438'>DRB1*0438</option>
<option value='DRB1_0439'>DRB1*0439</option>
<option value='DRB1_0440'>DRB1*0440</option>
<option value='DRB1_0441'>DRB1*0441</option>
<option value='DRB1_0442'>DRB1*0442</option>
<option value='DRB1_0443'>DRB1*0443</option>
<option value='DRB1_0444'>DRB1*0444</option>
<option value='DRB1_0445'>DRB1*0445</option>
<option value='DRB1_0446'>DRB1*0446</option>
<option value='DRB1_0447'>DRB1*0447</option>
<option value='DRB1_0448'>DRB1*0448</option>
<option value='DRB1_0449'>DRB1*0449</option>
<option value='DRB1_0450'>DRB1*0450</option>
<option value='DRB1_0451'>DRB1*0451</option>
<option value='DRB1_0452'>DRB1*0452</option>
<option value='DRB1_0453'>DRB1*0453</option>
<option value='DRB1_0454'>DRB1*0454</option>
<option value='DRB1_0455'>DRB1*0455</option>
<option value='DRB1_0456'>DRB1*0456</option>
<option value='DRB1_0457'>DRB1*0457</option>
<option value='DRB1_0458'>DRB1*0458</option>
<option value='DRB1_0459'>DRB1*0459</option>
<option value='DRB1_0460'>DRB1*0460</option>
<option value='DRB1_0461'>DRB1*0461</option>
<option value='DRB1_0462'>DRB1*0462</option>
<option value='DRB1_0463'>DRB1*0463</option>
<option value='DRB1_0464'>DRB1*0464</option>
<option value='DRB1_0465'>DRB1*0465</option>
<option value='DRB1_0466'>DRB1*0466</option>
<option value='DRB1_0467'>DRB1*0467</option>
<option value='DRB1_0468'>DRB1*0468</option>
<option value='DRB1_0469'>DRB1*0469</option>
<option value='DRB1_0470'>DRB1*0470</option>
<option value='DRB1_0471'>DRB1*0471</option>
<option value='DRB1_0472'>DRB1*0472</option>
<option value='DRB1_0473'>DRB1*0473</option>
<option value='DRB1_0474'>DRB1*0474</option>
<option value='DRB1_0475'>DRB1*0475</option>
<option value='DRB1_0476'>DRB1*0476</option>
<option value='DRB1_0477'>DRB1*0477</option>
<option value='DRB1_0478'>DRB1*0478</option>
<option value='DRB1_0479'>DRB1*0479</option>
<option value='DRB1_0480'>DRB1*0480</option>
<option value='DRB1_0482'>DRB1*0482</option>
<option value='DRB1_0483'>DRB1*0483</option>
<option value='DRB1_0484'>DRB1*0484</option>
<option value='DRB1_0485'>DRB1*0485</option>
<option value='DRB1_0486'>DRB1*0486</option>
<option value='DRB1_0487'>DRB1*0487</option>
<option value='DRB1_0488'>DRB1*0488</option>
<option value='DRB1_0489'>DRB1*0489</option>
<option value='DRB1_0491'>DRB1*0491</option>
<option value='DRB1_0701'>DRB1*0701</option>
<option value='DRB1_0703'>DRB1*0703</option>
<option value='DRB1_0704'>DRB1*0704</option>
<option value='DRB1_0705'>DRB1*0705</option>
<option value='DRB1_0706'>DRB1*0706</option>
<option value='DRB1_0707'>DRB1*0707</option>
<option value='DRB1_0708'>DRB1*0708</option>
<option value='DRB1_0709'>DRB1*0709</option>
<option value='DRB1_0711'>DRB1*0711</option>
<option value='DRB1_0712'>DRB1*0712</option>
<option value='DRB1_0713'>DRB1*0713</option>
<option value='DRB1_0714'>DRB1*0714</option>
<option value='DRB1_0715'>DRB1*0715</option>
<option value='DRB1_0716'>DRB1*0716</option>
<option value='DRB1_0717'>DRB1*0717</option>
<option value='DRB1_0719'>DRB1*0719</option>
<option value='DRB1_0801'>DRB1*0801</option>
<option value='DRB1_0802'>DRB1*0802</option>
<option value='DRB1_0803'>DRB1*0803</option>
<option value='DRB1_0804'>DRB1*0804</option>
<option value='DRB1_0805'>DRB1*0805</option>
<option value='DRB1_0806'>DRB1*0806</option>
<option value='DRB1_0807'>DRB1*0807</option>
<option value='DRB1_0808'>DRB1*0808</option>
<option value='DRB1_0809'>DRB1*0809</option>
<option value='DRB1_0810'>DRB1*0810</option>
<option value='DRB1_0811'>DRB1*0811</option>
<option value='DRB1_0812'>DRB1*0812</option>
<option value='DRB1_0813'>DRB1*0813</option>
<option value='DRB1_0814'>DRB1*0814</option>
<option value='DRB1_0815'>DRB1*0815</option>
<option value='DRB1_0816'>DRB1*0816</option>
<option value='DRB1_0818'>DRB1*0818</option>
<option value='DRB1_0819'>DRB1*0819</option>
<option value='DRB1_0820'>DRB1*0820</option>
<option value='DRB1_0821'>DRB1*0821</option>
<option value='DRB1_0822'>DRB1*0822</option>
<option value='DRB1_0823'>DRB1*0823</option>
<option value='DRB1_0824'>DRB1*0824</option>
<option value='DRB1_0825'>DRB1*0825</option>
<option value='DRB1_0826'>DRB1*0826</option>
<option value='DRB1_0827'>DRB1*0827</option>
<option value='DRB1_0828'>DRB1*0828</option>
<option value='DRB1_0829'>DRB1*0829</option>
<option value='DRB1_0830'>DRB1*0830</option>
<option value='DRB1_0831'>DRB1*0831</option>
<option value='DRB1_0832'>DRB1*0832</option>
<option value='DRB1_0833'>DRB1*0833</option>
<option value='DRB1_0834'>DRB1*0834</option>
<option value='DRB1_0835'>DRB1*0835</option>
<option value='DRB1_0836'>DRB1*0836</option>
<option value='DRB1_0837'>DRB1*0837</option>
<option value='DRB1_0838'>DRB1*0838</option>
<option value='DRB1_0839'>DRB1*0839</option>
<option value='DRB1_0840'>DRB1*0840</option>
<option value='DRB1_0901'>DRB1*0901</option>
<option value='DRB1_0902'>DRB1*0902</option>
<option value='DRB1_0903'>DRB1*0903</option>
<option value='DRB1_0904'>DRB1*0904</option>
<option value='DRB1_0905'>DRB1*0905</option>
<option value='DRB1_0906'>DRB1*0906</option>
<option value='DRB1_0907'>DRB1*0907</option>
<option value='DRB1_0908'>DRB1*0908</option>
<option value='DRB1_0909'>DRB1*0909</option>
<option value='DRB1_1001'>DRB1*1001</option>
<option value='DRB1_1002'>DRB1*1002</option>
<option value='DRB1_1003'>DRB1*1003</option>
<option value='DRB1_1101'>DRB1*1101</option>
<option value='DRB1_1102'>DRB1*1102</option>
<option value='DRB1_1103'>DRB1*1103</option>
<option value='DRB1_1104'>DRB1*1104</option>
<option value='DRB1_1105'>DRB1*1105</option>
<option value='DRB1_1106'>DRB1*1106</option>
<option value='DRB1_1107'>DRB1*1107</option>
<option value='DRB1_1108'>DRB1*1108</option>
<option value='DRB1_1109'>DRB1*1109</option>
<option value='DRB1_1110'>DRB1*1110</option>
<option value='DRB1_1111'>DRB1*1111</option>
<option value='DRB1_1112'>DRB1*1112</option>
<option value='DRB1_1113'>DRB1*1113</option>
<option value='DRB1_1114'>DRB1*1114</option>
<option value='DRB1_1115'>DRB1*1115</option>
<option value='DRB1_1116'>DRB1*1116</option>
<option value='DRB1_1117'>DRB1*1117</option>
<option value='DRB1_1118'>DRB1*1118</option>
<option value='DRB1_1119'>DRB1*1119</option>
<option value='DRB1_1120'>DRB1*1120</option>
<option value='DRB1_1121'>DRB1*1121</option>
<option value='DRB1_1124'>DRB1*1124</option>
<option value='DRB1_1125'>DRB1*1125</option>
<option value='DRB1_1127'>DRB1*1127</option>
<option value='DRB1_1128'>DRB1*1128</option>
<option value='DRB1_1129'>DRB1*1129</option>
<option value='DRB1_1130'>DRB1*1130</option>
<option value='DRB1_1131'>DRB1*1131</option>
<option value='DRB1_1132'>DRB1*1132</option>
<option value='DRB1_1133'>DRB1*1133</option>
<option value='DRB1_1134'>DRB1*1134</option>
<option value='DRB1_1135'>DRB1*1135</option>
<option value='DRB1_1136'>DRB1*1136</option>
<option value='DRB1_1137'>DRB1*1137</option>
<option value='DRB1_1138'>DRB1*1138</option>
<option value='DRB1_1139'>DRB1*1139</option>
<option value='DRB1_1141'>DRB1*1141</option>
<option value='DRB1_1142'>DRB1*1142</option>
<option value='DRB1_1143'>DRB1*1143</option>
<option value='DRB1_1144'>DRB1*1144</option>
<option value='DRB1_1145'>DRB1*1145</option>
<option value='DRB1_1146'>DRB1*1146</option>
<option value='DRB1_1147'>DRB1*1147</option>
<option value='DRB1_1148'>DRB1*1148</option>
<option value='DRB1_1149'>DRB1*1149</option>
<option value='DRB1_1150'>DRB1*1150</option>
<option value='DRB1_1151'>DRB1*1151</option>
<option value='DRB1_1152'>DRB1*1152</option>
<option value='DRB1_1153'>DRB1*1153</option>
<option value='DRB1_1154'>DRB1*1154</option>
<option value='DRB1_1155'>DRB1*1155</option>
<option value='DRB1_1156'>DRB1*1156</option>
<option value='DRB1_1157'>DRB1*1157</option>
<option value='DRB1_1158'>DRB1*1158</option>
<option value='DRB1_1159'>DRB1*1159</option>
<option value='DRB1_1160'>DRB1*1160</option>
<option value='DRB1_1161'>DRB1*1161</option>
<option value='DRB1_1162'>DRB1*1162</option>
<option value='DRB1_1163'>DRB1*1163</option>
<option value='DRB1_1164'>DRB1*1164</option>
<option value='DRB1_1165'>DRB1*1165</option>
<option value='DRB1_1166'>DRB1*1166</option>
<option value='DRB1_1167'>DRB1*1167</option>
<option value='DRB1_1168'>DRB1*1168</option>
<option value='DRB1_1169'>DRB1*1169</option>
<option value='DRB1_1170'>DRB1*1170</option>
<option value='DRB1_1172'>DRB1*1172</option>
<option value='DRB1_1173'>DRB1*1173</option>
<option value='DRB1_1174'>DRB1*1174</option>
<option value='DRB1_1175'>DRB1*1175</option>
<option value='DRB1_1176'>DRB1*1176</option>
<option value='DRB1_1177'>DRB1*1177</option>
<option value='DRB1_1178'>DRB1*1178</option>
<option value='DRB1_1179'>DRB1*1179</option>
<option value='DRB1_1180'>DRB1*1180</option>
<option value='DRB1_1181'>DRB1*1181</option>
<option value='DRB1_1182'>DRB1*1182</option>
<option value='DRB1_1183'>DRB1*1183</option>
<option value='DRB1_1184'>DRB1*1184</option>
<option value='DRB1_1185'>DRB1*1185</option>
<option value='DRB1_1186'>DRB1*1186</option>
<option value='DRB1_1187'>DRB1*1187</option>
<option value='DRB1_1188'>DRB1*1188</option>
<option value='DRB1_1189'>DRB1*1189</option>
<option value='DRB1_1190'>DRB1*1190</option>
<option value='DRB1_1191'>DRB1*1191</option>
<option value='DRB1_1192'>DRB1*1192</option>
<option value='DRB1_1193'>DRB1*1193</option>
<option value='DRB1_1194'>DRB1*1194</option>
<option value='DRB1_1195'>DRB1*1195</option>
<option value='DRB1_1196'>DRB1*1196</option>
<option value='DRB1_1201'>DRB1*1201</option>
<option value='DRB1_1202'>DRB1*1202</option>
<option value='DRB1_1203'>DRB1*1203</option>
<option value='DRB1_1204'>DRB1*1204</option>
<option value='DRB1_1205'>DRB1*1205</option>
<option value='DRB1_1206'>DRB1*1206</option>
<option value='DRB1_1207'>DRB1*1207</option>
<option value='DRB1_1208'>DRB1*1208</option>
<option value='DRB1_1209'>DRB1*1209</option>
<option value='DRB1_1210'>DRB1*1210</option>
<option value='DRB1_1211'>DRB1*1211</option>
<option value='DRB1_1212'>DRB1*1212</option>
<option value='DRB1_1213'>DRB1*1213</option>
<option value='DRB1_1214'>DRB1*1214</option>
<option value='DRB1_1215'>DRB1*1215</option>
<option value='DRB1_1216'>DRB1*1216</option>
<option value='DRB1_1217'>DRB1*1217</option>
<option value='DRB1_1218'>DRB1*1218</option>
<option value='DRB1_1219'>DRB1*1219</option>
<option value='DRB1_1220'>DRB1*1220</option>
<option value='DRB1_1221'>DRB1*1221</option>
<option value='DRB1_1222'>DRB1*1222</option>
<option value='DRB1_1223'>DRB1*1223</option>
<option value='DRB1_1301'>DRB1*1301</option>
<option value='DRB1_1302'>DRB1*1302</option>
<option value='DRB1_1303'>DRB1*1303</option>
<option value='DRB1_1304'>DRB1*1304</option>
<option value='DRB1_1305'>DRB1*1305</option>
<option value='DRB1_1306'>DRB1*1306</option>
<option value='DRB1_1307'>DRB1*1307</option>
<option value='DRB1_1308'>DRB1*1308</option>
<option value='DRB1_1309'>DRB1*1309</option>
<option value='DRB1_1310'>DRB1*1310</option>
<option value='DRB1_13100'>DRB1*13100</option>
<option value='DRB1_13101'>DRB1*13101</option>
<option value='DRB1_1311'>DRB1*1311</option>
<option value='DRB1_1312'>DRB1*1312</option>
<option value='DRB1_1313'>DRB1*1313</option>
<option value='DRB1_1314'>DRB1*1314</option>
<option value='DRB1_1315'>DRB1*1315</option>
<option value='DRB1_1316'>DRB1*1316</option>
<option value='DRB1_1317'>DRB1*1317</option>
<option value='DRB1_1318'>DRB1*1318</option>
<option value='DRB1_1319'>DRB1*1319</option>
<option value='DRB1_1320'>DRB1*1320</option>
<option value='DRB1_1321'>DRB1*1321</option>
<option value='DRB1_1322'>DRB1*1322</option>
<option value='DRB1_1323'>DRB1*1323</option>
<option value='DRB1_1324'>DRB1*1324</option>
<option value='DRB1_1326'>DRB1*1326</option>
<option value='DRB1_1327'>DRB1*1327</option>
<option value='DRB1_1329'>DRB1*1329</option>
<option value='DRB1_1330'>DRB1*1330</option>
<option value='DRB1_1331'>DRB1*1331</option>
<option value='DRB1_1332'>DRB1*1332</option>
<option value='DRB1_1333'>DRB1*1333</option>
<option value='DRB1_1334'>DRB1*1334</option>
<option value='DRB1_1335'>DRB1*1335</option>
<option value='DRB1_1336'>DRB1*1336</option>
<option value='DRB1_1337'>DRB1*1337</option>
<option value='DRB1_1338'>DRB1*1338</option>
<option value='DRB1_1339'>DRB1*1339</option>
<option value='DRB1_1341'>DRB1*1341</option>
<option value='DRB1_1342'>DRB1*1342</option>
<option value='DRB1_1343'>DRB1*1343</option>
<option value='DRB1_1344'>DRB1*1344</option>
<option value='DRB1_1346'>DRB1*1346</option>
<option value='DRB1_1347'>DRB1*1347</option>
<option value='DRB1_1348'>DRB1*1348</option>
<option value='DRB1_1349'>DRB1*1349</option>
<option value='DRB1_1350'>DRB1*1350</option>
<option value='DRB1_1351'>DRB1*1351</option>
<option value='DRB1_1352'>DRB1*1352</option>
<option value='DRB1_1353'>DRB1*1353</option>
<option value='DRB1_1354'>DRB1*1354</option>
<option value='DRB1_1355'>DRB1*1355</option>
<option value='DRB1_1356'>DRB1*1356</option>
<option value='DRB1_1357'>DRB1*1357</option>
<option value='DRB1_1358'>DRB1*1358</option>
<option value='DRB1_1359'>DRB1*1359</option>
<option value='DRB1_1360'>DRB1*1360</option>
<option value='DRB1_1361'>DRB1*1361</option>
<option value='DRB1_1362'>DRB1*1362</option>
<option value='DRB1_1363'>DRB1*1363</option>
<option value='DRB1_1364'>DRB1*1364</option>
<option value='DRB1_1365'>DRB1*1365</option>
<option value='DRB1_1366'>DRB1*1366</option>
<option value='DRB1_1367'>DRB1*1367</option>
<option value='DRB1_1368'>DRB1*1368</option>
<option value='DRB1_1369'>DRB1*1369</option>
<option value='DRB1_1370'>DRB1*1370</option>
<option value='DRB1_1371'>DRB1*1371</option>
<option value='DRB1_1372'>DRB1*1372</option>
<option value='DRB1_1373'>DRB1*1373</option>
<option value='DRB1_1374'>DRB1*1374</option>
<option value='DRB1_1375'>DRB1*1375</option>
<option value='DRB1_1376'>DRB1*1376</option>
<option value='DRB1_1377'>DRB1*1377</option>
<option value='DRB1_1378'>DRB1*1378</option>
<option value='DRB1_1379'>DRB1*1379</option>
<option value='DRB1_1380'>DRB1*1380</option>
<option value='DRB1_1381'>DRB1*1381</option>
<option value='DRB1_1382'>DRB1*1382</option>
<option value='DRB1_1383'>DRB1*1383</option>
<option value='DRB1_1384'>DRB1*1384</option>
<option value='DRB1_1385'>DRB1*1385</option>
<option value='DRB1_1386'>DRB1*1386</option>
<option value='DRB1_1387'>DRB1*1387</option>
<option value='DRB1_1388'>DRB1*1388</option>
<option value='DRB1_1389'>DRB1*1389</option>
<option value='DRB1_1390'>DRB1*1390</option>
<option value='DRB1_1391'>DRB1*1391</option>
<option value='DRB1_1392'>DRB1*1392</option>
<option value='DRB1_1393'>DRB1*1393</option>
<option value='DRB1_1394'>DRB1*1394</option>
<option value='DRB1_1395'>DRB1*1395</option>
<option value='DRB1_1396'>DRB1*1396</option>
<option value='DRB1_1397'>DRB1*1397</option>
<option value='DRB1_1398'>DRB1*1398</option>
<option value='DRB1_1399'>DRB1*1399</option>
<option value='DRB1_1401'>DRB1*1401</option>
<option value='DRB1_1402'>DRB1*1402</option>
<option value='DRB1_1403'>DRB1*1403</option>
<option value='DRB1_1404'>DRB1*1404</option>
<option value='DRB1_1405'>DRB1*1405</option>
<option value='DRB1_1406'>DRB1*1406</option>
<option value='DRB1_1407'>DRB1*1407</option>
<option value='DRB1_1408'>DRB1*1408</option>
<option value='DRB1_1409'>DRB1*1409</option>
<option value='DRB1_1410'>DRB1*1410</option>
<option value='DRB1_1411'>DRB1*1411</option>
<option value='DRB1_1412'>DRB1*1412</option>
<option value='DRB1_1413'>DRB1*1413</option>
<option value='DRB1_1414'>DRB1*1414</option>
<option value='DRB1_1415'>DRB1*1415</option>
<option value='DRB1_1416'>DRB1*1416</option>
<option value='DRB1_1417'>DRB1*1417</option>
<option value='DRB1_1418'>DRB1*1418</option>
<option value='DRB1_1419'>DRB1*1419</option>
<option value='DRB1_1420'>DRB1*1420</option>
<option value='DRB1_1421'>DRB1*1421</option>
<option value='DRB1_1422'>DRB1*1422</option>
<option value='DRB1_1423'>DRB1*1423</option>
<option value='DRB1_1424'>DRB1*1424</option>
<option value='DRB1_1425'>DRB1*1425</option>
<option value='DRB1_1426'>DRB1*1426</option>
<option value='DRB1_1427'>DRB1*1427</option>
<option value='DRB1_1428'>DRB1*1428</option>
<option value='DRB1_1429'>DRB1*1429</option>
<option value='DRB1_1430'>DRB1*1430</option>
<option value='DRB1_1431'>DRB1*1431</option>
<option value='DRB1_1432'>DRB1*1432</option>
<option value='DRB1_1433'>DRB1*1433</option>
<option value='DRB1_1434'>DRB1*1434</option>
<option value='DRB1_1435'>DRB1*1435</option>
<option value='DRB1_1436'>DRB1*1436</option>
<option value='DRB1_1437'>DRB1*1437</option>
<option value='DRB1_1438'>DRB1*1438</option>
<option value='DRB1_1439'>DRB1*1439</option>
<option value='DRB1_1440'>DRB1*1440</option>
<option value='DRB1_1441'>DRB1*1441</option>
<option value='DRB1_1442'>DRB1*1442</option>
<option value='DRB1_1443'>DRB1*1443</option>
<option value='DRB1_1444'>DRB1*1444</option>
<option value='DRB1_1445'>DRB1*1445</option>
<option value='DRB1_1446'>DRB1*1446</option>
<option value='DRB1_1447'>DRB1*1447</option>
<option value='DRB1_1448'>DRB1*1448</option>
<option value='DRB1_1449'>DRB1*1449</option>
<option value='DRB1_1450'>DRB1*1450</option>
<option value='DRB1_1451'>DRB1*1451</option>
<option value='DRB1_1452'>DRB1*1452</option>
<option value='DRB1_1453'>DRB1*1453</option>
<option value='DRB1_1454'>DRB1*1454</option>
<option value='DRB1_1455'>DRB1*1455</option>
<option value='DRB1_1456'>DRB1*1456</option>
<option value='DRB1_1457'>DRB1*1457</option>
<option value='DRB1_1458'>DRB1*1458</option>
<option value='DRB1_1459'>DRB1*1459</option>
<option value='DRB1_1460'>DRB1*1460</option>
<option value='DRB1_1461'>DRB1*1461</option>
<option value='DRB1_1462'>DRB1*1462</option>
<option value='DRB1_1463'>DRB1*1463</option>
<option value='DRB1_1464'>DRB1*1464</option>
<option value='DRB1_1465'>DRB1*1465</option>
<option value='DRB1_1467'>DRB1*1467</option>
<option value='DRB1_1468'>DRB1*1468</option>
<option value='DRB1_1469'>DRB1*1469</option>
<option value='DRB1_1470'>DRB1*1470</option>
<option value='DRB1_1471'>DRB1*1471</option>
<option value='DRB1_1472'>DRB1*1472</option>
<option value='DRB1_1473'>DRB1*1473</option>
<option value='DRB1_1474'>DRB1*1474</option>
<option value='DRB1_1475'>DRB1*1475</option>
<option value='DRB1_1476'>DRB1*1476</option>
<option value='DRB1_1477'>DRB1*1477</option>
<option value='DRB1_1478'>DRB1*1478</option>
<option value='DRB1_1479'>DRB1*1479</option>
<option value='DRB1_1480'>DRB1*1480</option>
<option value='DRB1_1481'>DRB1*1481</option>
<option value='DRB1_1482'>DRB1*1482</option>
<option value='DRB1_1483'>DRB1*1483</option>
<option value='DRB1_1484'>DRB1*1484</option>
<option value='DRB1_1485'>DRB1*1485</option>
<option value='DRB1_1486'>DRB1*1486</option>
<option value='DRB1_1487'>DRB1*1487</option>
<option value='DRB1_1488'>DRB1*1488</option>
<option value='DRB1_1489'>DRB1*1489</option>
<option value='DRB1_1490'>DRB1*1490</option>
<option value='DRB1_1491'>DRB1*1491</option>
<option value='DRB1_1493'>DRB1*1493</option>
<option value='DRB1_1494'>DRB1*1494</option>
<option value='DRB1_1495'>DRB1*1495</option>
<option value='DRB1_1496'>DRB1*1496</option>
<option value='DRB1_1497'>DRB1*1497</option>
<option value='DRB1_1498'>DRB1*1498</option>
<option value='DRB1_1499'>DRB1*1499</option>
<option value='DRB1_1501'>DRB1*1501</option>
<option value='DRB1_1502'>DRB1*1502</option>
<option value='DRB1_1503'>DRB1*1503</option>
<option value='DRB1_1504'>DRB1*1504</option>
<option value='DRB1_1505'>DRB1*1505</option>
<option value='DRB1_1506'>DRB1*1506</option>
<option value='DRB1_1507'>DRB1*1507</option>
<option value='DRB1_1508'>DRB1*1508</option>
<option value='DRB1_1509'>DRB1*1509</option>
<option value='DRB1_1510'>DRB1*1510</option>
<option value='DRB1_1511'>DRB1*1511</option>
<option value='DRB1_1512'>DRB1*1512</option>
<option value='DRB1_1513'>DRB1*1513</option>
<option value='DRB1_1514'>DRB1*1514</option>
<option value='DRB1_1515'>DRB1*1515</option>
<option value='DRB1_1516'>DRB1*1516</option>
<option value='DRB1_1518'>DRB1*1518</option>
<option value='DRB1_1519'>DRB1*1519</option>
<option value='DRB1_1520'>DRB1*1520</option>
<option value='DRB1_1521'>DRB1*1521</option>
<option value='DRB1_1522'>DRB1*1522</option>
<option value='DRB1_1523'>DRB1*1523</option>
<option value='DRB1_1524'>DRB1*1524</option>
<option value='DRB1_1525'>DRB1*1525</option>
<option value='DRB1_1526'>DRB1*1526</option>
<option value='DRB1_1527'>DRB1*1527</option>
<option value='DRB1_1528'>DRB1*1528</option>
<option value='DRB1_1529'>DRB1*1529</option>
<option value='DRB1_1530'>DRB1*1530</option>
<option value='DRB1_1531'>DRB1*1531</option>
<option value='DRB1_1532'>DRB1*1532</option>
<option value='DRB1_1533'>DRB1*1533</option>
<option value='DRB1_1534'>DRB1*1534</option>
<option value='DRB1_1535'>DRB1*1535</option>
<option value='DRB1_1536'>DRB1*1536</option>
<option value='DRB1_1537'>DRB1*1537</option>
<option value='DRB1_1538'>DRB1*1538</option>
<option value='DRB1_1539'>DRB1*1539</option>
<option value='DRB1_1540'>DRB1*1540</option>
<option value='DRB1_1541'>DRB1*1541</option>
<option value='DRB1_1542'>DRB1*1542</option>
<option value='DRB1_1543'>DRB1*1543</option>
<option value='DRB1_1544'>DRB1*1544</option>
<option value='DRB1_1545'>DRB1*1545</option>
<option value='DRB1_1546'>DRB1*1546</option>
<option value='DRB1_1547'>DRB1*1547</option>
<option value='DRB1_1548'>DRB1*1548</option>
<option value='DRB1_1549'>DRB1*1549</option>
<option value='DRB1_1601'>DRB1*1601</option>
<option value='DRB1_1602'>DRB1*1602</option>
<option value='DRB1_1603'>DRB1*1603</option>
<option value='DRB1_1604'>DRB1*1604</option>
<option value='DRB1_1605'>DRB1*1605</option>
<option value='DRB1_1607'>DRB1*1607</option>
<option value='DRB1_1608'>DRB1*1608</option>
<option value='DRB1_1609'>DRB1*1609</option>
<option value='DRB1_1610'>DRB1*1610</option>
<option value='DRB1_1611'>DRB1*1611</option>
<option value='DRB1_1612'>DRB1*1612</option>
<option value='DRB1_1614'>DRB1*1614</option>
<option value='DRB1_1615'>DRB1*1615</option>
<option value='DRB1_1616'>DRB1*1616</option>
</select>
</span>
<span id='$ids[slave_B3]' style='display:none'>
<select name='$names[slaveB]' size='12' multiple='' onclick='NetMHCII_addAlleleB(this)'>
<option value='DRB3_0101'>DRB3*0101</option>
<option value='DRB3_0104'>DRB3*0104</option>
<option value='DRB3_0105'>DRB3*0105</option>
<option value='DRB3_0108'>DRB3*0108</option>
<option value='DRB3_0109'>DRB3*0109</option>
<option value='DRB3_0111'>DRB3*0111</option>
<option value='DRB3_0112'>DRB3*0112</option>
<option value='DRB3_0113'>DRB3*0113</option>
<option value='DRB3_0114'>DRB3*0114</option>
<option value='DRB3_0201'>DRB3*0201</option>
<option value='DRB3_0202'>DRB3*0202</option>
<option value='DRB3_0204'>DRB3*0204</option>
<option value='DRB3_0205'>DRB3*0205</option>
<option value='DRB3_0209'>DRB3*0209</option>
<option value='DRB3_0210'>DRB3*0210</option>
<option value='DRB3_0211'>DRB3*0211</option>
<option value='DRB3_0212'>DRB3*0212</option>
<option value='DRB3_0213'>DRB3*0213</option>
<option value='DRB3_0214'>DRB3*0214</option>
<option value='DRB3_0215'>DRB3*0215</option>
<option value='DRB3_0216'>DRB3*0216</option>
<option value='DRB3_0217'>DRB3*0217</option>
<option value='DRB3_0218'>DRB3*0218</option>
<option value='DRB3_0219'>DRB3*0219</option>
<option value='DRB3_0220'>DRB3*0220</option>
<option value='DRB3_0221'>DRB3*0221</option>
<option value='DRB3_0222'>DRB3*0222</option>
<option value='DRB3_0223'>DRB3*0223</option>
<option value='DRB3_0224'>DRB3*0224</option>
<option value='DRB3_0225'>DRB3*0225</option>
<option value='DRB3_0301'>DRB3*0301</option>
<option value='DRB3_0303'>DRB3*0303</option>
</select>
</span>
<span id='$ids[slave_B4]' style='display:none'>
<select name='$names[slaveB]' size='12' multiple='' onclick='NetMHCII_addAlleleB(this)'>
<option value='DRB4_0101'>DRB4*0101</option>
<option value='DRB4_0103'>DRB4*0103</option>
</select>
</span>
<span id='$ids[slave_B5]' style='display:none'>
<select name='$names[slaveB]' size='12' multiple='' onclick='NetMHCII_addAlleleB(this)'>
<option value='DRB5_0101'>DRB5*0101</option>
<option value='DRB5_0102'>DRB5*0102</option>
<option value='DRB5_0103'>DRB5*0103</option>
<option value='DRB5_0104'>DRB5*0104</option>
<option value='DRB5_0105'>DRB5*0105</option>
<option value='DRB5_0106'>DRB5*0106</option>
<option value='DRB5_0108N'>DRB5*0108N</option>
<option value='DRB5_0111'>DRB5*0111</option>
<option value='DRB5_0112'>DRB5*0112</option>
<option value='DRB5_0113'>DRB5*0113</option>
<option value='DRB5_0114'>DRB5*0114</option>
<option value='DRB5_0202'>DRB5*0202</option>
<option value='DRB5_0203'>DRB5*0203</option>
<option value='DRB5_0204'>DRB5*0204</option>
<option value='DRB5_0205'>DRB5*0205</option>
</select>
</span>
<span id='$ids[slave_B6]' style='display:none'>
<select name='$names[slaveB]' size='12' multiple='' onclick='NetMHCII_addAlleleB(this)'>
<option value='H-2-IAb'>H-2-IAb</option>
<option value='H-2-IAd'>H-2-IAd</option>
</select>
</span>
<br>
<br>
<b>o escriba mol&eacuteculas nombres (por ejemplo DRB1_1121 o HLA-DQA10511-DQB10404) separados por comas (sin espacios). Max. 20 alelos por sumisi&oacuten. </b>.&nbsp;  <br>
<input name='$names[allele]' type='text' value='' size='100' onblur='NetMHCII_cleanList()'><br>
Para la lista de nombres de mol&eacuteculas permitidos haga clic aqu&iacute:  <a href='http://www.cbs.dtu.dk/services/NetMHCIIpan/alleles_name.list' target='_blank'>Lista de nombres de mol&eacuteculas del MHC.</a> 
</div>

<br>
<div id='$ids[fullalpha]'> <!-- FULL LENGTH BETA CHAIN -->
o pegar una sola secuencia de prote&iacutena de longitud completa de MHC <b>cadena alfa</b> en formato 
FASTA
 en el campo de abajo: 
<br>
<textarea name='$names[MHCSEQPASTEa]' rows='4' cols='64'></textarea>
<br>
<i>o enviar un archivo que contiene una secuencia de longitud completa de MHC <b>cadena alfa</b> en formato
FASTA
directamente desde el disco local:</i>
<input name='$names[MHCSEQSUBa]' size='40' type='file'>
<br><br><br>
</div>

o pegar una sola secuencia de prote&iacutena de longitud completa de <b>la cadena beta</b> del MHC en formato
FASTA
en el campo de abajo: 
<br>
<textarea name='$names[MHCSEQPASTEb]' rows='4' cols='64'></textarea>
<br>
<i>o enviar un archivo que contiene una secuencia de longitud completa de <b>la cadena beta</b> del MHC en formato
FASTA
directamente desde el disco local: </i>
<input name='$names[MHCSEQSUBb]' size='40' type='file'>
<br>



<br>
<b>Umbral para strong binder (% Rank)</b>&nbsp;
<input name='$names[thrs]' type='text' value='0.5' size='5'>

<b>Umbral para strong binder (IC50)</b>&nbsp;
<input name='$names[thas]' type='text' value='50' size='5'>

<br>

<b>Umbral para weak binder (% Rank)</b>&nbsp;
<input name='$names[thrw]' type='text' value='2' size='5'>

<b>Umbral para weak binder (IC50)</b>&nbsp;
<input name='$names[thaw]' type='text' value='500' size='5'>

<br>
<br>

<b>Filter output </b>
<select name='$names[filt]' size='1' onchange=\"NetMHCII_showselect2(this, 'NetMHCII_slaveC')\"> 
<option value='0' selected=''>No</option>
<option value='1'>Yes</option>
</select>

<div id='$ids[slaveC0]' style='display:none'>
</div>

<div id='$ids[slaveC1]' style='display:none'>

<b>Umbral de filtrado para %Rank  </b>&nbsp;
<input name='$names[thrf]' type='text' value='2' size='5'>

<b>Umbral de filtrado para IC50  </b>&nbsp;
<input name='$names[thaf]' type='text' value='500' size='5'>
</div>

<br>
<br>
<b>Utilice el modo r&aacutepido (se recomienda para grandes c&aacutelculos)</b>&nbsp; <input name='$names[fast]' type='checkbox'>
<br><br>
<b>Imprimir s&oacutelo el n&uacutecleo de uni&oacuten m&aacutes fuerte</b>&nbsp; <input name='$names[unique]' type='checkbox'>
<br><br>

<p>
<b>Restricciones:</b>
<br>
<i>En la mayor&iacutea de las 5.000 secuencias por sumisi&oacuten; cada secuencia no m&aacutes de 20 000 amino&aacutecidos y no menos de 8 amino&aacutecidos. Max 20 MHC alelos por sumisi&oacuten.</i>
</p><p>
<b>Confidencialidad:</b>
<br>
<i>Las secuencias se mantienen confidenciales y ser&aacuten eliminados despu&eacutes de su procesamiento.</i>
</p>" );
  }
}
?>
