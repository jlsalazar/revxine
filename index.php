<?php
/*
 * index.php REVXINE system, main page of REVXINE Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('class/classes.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Consulta</title>
<meta http-e quiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" media="all"
	href="css/style.css?t" />
<script type="text/javascript" src="js/script.js"> </script>
</head>
<body>
	<div id="header">
		<IMG SRC="img/cib.jpg" WIDTH=80 HEIGHT=80 align="left"> <IMG
			SRC="img/itlp.jpg" WIDTH=80 HEIGHT=70 align="left" HSPACE=20>
				<h3>REVXINE v1.0 : Conjunto de herramientas para vacunologia inversa</h3>
	
	</div>

	<div id="bar">
		<a class="buttonlink" href="index.php">Inicio</a> <a
			class="buttonlink" href="instructions.php">Instrucciones</a> <a
			class="buttonlink" href="servers.php">Servidores</a> <a
			class="buttonlink" href="contact.php">Contacto</a>
	</div>

	<div id="section">
		<div id="bloquea">
			<div id="bloquea_img">
				<img src="img/reload8_1.gif">
			
			</div>
		</div>
		<form enctype="multipart/form-data" action="results.php" method="POST">
			<div id="section_left">
				<h3>PRESENTAR SECUENCIA(S)</h3>
				<p>
					<b>Tipo de entrada </b> <select name="inp" size="1">
						<option value="0" selected="">Fasta</option>
					</select>
				</p>
				<div id="slaveA0" style="display: show">
					<i>Pegue una sola secuencia o varias secuencias en formato FASTA en
						el campo de abajo:</i> <br> <textarea name="SEQPASTE" rows="8"
							cols="50px"></textarea> <br> <i>o enviar un archivo en formato
								FASTA directamente desde su disco local:</i> <br> <input
								name="SEQSUB" size="40px" type="file"></input>
				
				</div>

				<h3>ANALIZAR SECUENCIA(S) CON:</h3>
				<i><b>Haz clic en el nombre para modificar los parametros. </b></i>
				<br>
					<table>
						<tr>
							<td colspan=2 width="50%"><i><b>Localizacion Subcelular</b></i></td>
						</tr>
						<tr>
							<td><input type="checkbox" name="Cello" value="true" checked> <a
									href="#" onclick="showoptions('3', 'server')">Cello</a><br></td>
							<td width="50%" align="right"><select name="LOCATION[]"
								style="width: 100%" size="4" multiple="multiple">
									<option value="Extracellular" selected="selected">Extracellular</option>
									<option value="Membrane" selected="selected">Membrane</option>
									<option value="CellWall">CellWall</option>
									<option value="OuterMembrane">OuterMembrane</option>
									<option value="InnerMembrane">InnerMembrane</option>
									<option value="Periplasmic">Periplasmic</option>
									<option value="Cytoplasmic">Cytoplasmic</option>
									<option value="Chloroplast">Chloroplast</option>
									<option value="Lysosomal">Lysosomal</option>
									<option value="ER">ER</option>
									<option value="Peroxisomal">Peroxisomal</option>
									<option value="Mitochondrial">Mitochondrial</option>
									<option value="Nuclear">Nuclear</option>
									<option value="Vacuole">Vacuole</option>
									<option value="Golgi">Golgi</option>
									<option value="Cytoskeletal">Cytoskeletal</option>
							</select><br> Confiabilidad: <input type="radio"
									name="RELIABILITY" value="normal" checked> Normal</input> <input
									type="radio" name="RELIABILITY" value="alta"> Alta<br></td>
						</tr>
						<tr>
							<td colspan=2><i><b>Union molecular MHCI</b></i></td>
						</tr>
						<tr>
							<td colspan=2><input type="checkbox" name="NetMHCI" value="true"
								checked> <a href="#" onclick="showoptions('0', 'server')">NetMHCI</a><br></td>
						</tr>
						<tr>
							<td colspan=2><i><b>Union molecular MHCII</b></i></td>
						</tr>
						<tr>
							<td colspan=2><input type="checkbox" name="NetMHCII" value="true"
								checked> <a href="#" onclick="showoptions('1', 'server')">NetMHCII</a><br></td>
						</tr>
						<tr>
							<td colspan=2><i><b>Localizacion de epitopos lineales de celulas
										B</b></i></td>
						</tr>
						<tr>
							<td colspan=2><input type="checkbox" name="BepiPred" value="true"
								checked> <a href="#" onclick="showoptions('2', 'server')">BepiPred</a><br></td>
						</tr>
					</table> <i>Para mas informacion visite la seccion<a
						href="servers.php">SERVIDORES</a>.
				</i> <br></br> <input type="submit" value="Submit"
					onclick="document.getElementById('bloquea').style.display='block';">
			
			</div>
			<div id="section_right">
				<div id="server0" style="display: none;">
							<?php
      $object0 = new NetMHCI ();
      $object0->initialize ();
      echo $object0->getForm ();
      unset ( $object0 );
      ?>
						</div>
				<div id="server1" style="display: none;">
							<?php
      $object1 = new NetMHCII ();
      $object1->initialize ();
      echo $object1->getForm ();
      unset ( $object1 );
      ?>
						</div>
				<div id="server2" style="display: none;">
							<?php
      $object2 = new BepiPred ();
      $object2->initialize ();
      echo $object2->getForm ();
      unset ( $object2 );
      ?>
						</div>
				<div id="server3" style="display: none;">
							<?php
      $object3 = new Cello ();
      $object3->initialize ();
      echo $object3->getForm ();
      unset ( $object3 );
      ?>
						</div>
			</div>
		</form>
	</div>

	<div id="footer">
		Instituto Tecnol&oacutegico de La Paz<br> Centro de Investigaciones
			Biol&oacutegicas del Noroeste, S.C. 
	
	</div>

</body>
</html>
