<?php
/*
 * PrintResults.php REVXINE system, print of REVXINE results Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
function printResults($array, $ERRORS) {
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Resultado</title>
<meta http-e quiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" media="all"
	href="css/style.css?t" />
<script>
			function showmenu(inp, divname,len) {
				var slavediv;
				var i;
				for (i=0;i<len;i++){
					slavediv=document.getElementById(divname+i);
					slavediv.style.display='none';
				}
				slavediv=document.getElementById(divname+inp);
				slavediv.style.display='block';
			}
			</script>
</head>
<body>

	<div id="header">
		<IMG SRC="img/cib.jpg" WIDTH=80 HEIGHT=80 align="left"> <IMG
			SRC="img/itlp.jpg" WIDTH=80 HEIGHT=70 align="left" HSPACE=20>
				<h3>REVXINE v1.0 : Conjunto de herramientas para vacunologia inversa</h3>
	
	</div>

	<div id="bar">
		<!--
				<a class="buttonlink" href="#">Inicio</a>
				<a class="buttonlink" href="#">Programa</a>
				<a class="buttonlink" href="#">Contacto</a>
			-->
	</div>

	<div id="section">
		<div id="section_header">
			<div id="section_left_header">Servidores</div>
			<div id="section_right_header">Resultados</div>
		</div>
		<div id="section_results">
			<div id="section_left_Results">
							<?php
  $i = 0;
  foreach ( $array as $server => $object ) {
    $space = ($server == 'Reportes') ? '<br>' : '';
    echo $space . "<a href='#' onclick=\"showmenu('$i', 'server'," . count ( $array ) . ")\">" . $server . "</a><br>";
    $i ++;
  }
  echo "</br></br></br></br></br></br><a href=http://posgrado.itlp.edu.mx/revxine>Regresar al inicio</a>";
  ?>
						</div>

			<div id="section_right_Results">
							<?php
  $i = 0;
  
  foreach ( $array as $server => $object ) {
    $titulo = "<div id='titulo'>$server</div>";
    
    if ($object->getResult_isOK () == 0)
      echo "<div id='server$i' style='display: none;'> $titulo" . $object->printContent () . "</div>";
    else
      echo "<div id='server$i' style='display: none;'> $titulo" . $ERRORS [$object->getResult_isOK ()] . "</div>";
    $i ++;
  }
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


<?php
}
?>

