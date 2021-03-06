<?php
/*
 * servers.php REVXINE system, info of REVXINE servers Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
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
		<div id="instructions">
			<h5>Instrucciones</h5>
			<b>1. Secuencias de entrada</b><br> Las secuencias deben estar
				escritas en correcto formato FASTA, encabezado con nombre,
				comentarios (opcionales) y secuencia, ejemplo: <br><br>>MAP0858<br>
							MRWTRRKPRSQTLTFAIEARCRECHYKATERAKVTTYPAERVADQLRPTPPAVPSKFGGLWILAVV<br>
								SASNSSTPAISPSAKCSRSAAVCQSSSTAPCIRLRSSRPSWSRADCSLAPLTSHSAPGYRAVHDRS<br>
									SYSAVCGTNAKALPVVRMKSSKFVLRSSVFAISCPLRHPCDLSELTRRSR<br>
										>MAP1609C comentarios opcionales<br>
											MTDLSEKVRAWGRRLLVGAAAAVTLPGLIGLAGGAATANAFSRPGLPVEYLQVPSAGMGRDIKVQF<br>
												QSGGNGSPAVYLLDGLRAQDDYNGWDINTPAFEWYYQSGLSVIMPVGGQSSFYADWYQPACGKAGC<br>
													STYKWETFLTSELPSYLASNKGVKRTGSAAVGISMSGSSAMILAVNHPDQFIYAGSLSALLDPSQG<br>
														MGPSLIGLAMGDAGGYKADAMWGPSSDPAWQRNDPSLHIPELVGHNTRLWVYCGNGTPSELGGANM<br>
															PAEFLENFVRSSNLKFQDAYNAAGGHNAVFNFNANGTHSWEYWGAQLNAMKPDLQGTLGASPGGGG<br><br>
																	Nota: Es importante el correcto nombre de la secuencia
																	porque de ello depende la congruencia de las tablas
																	interpretadas por REVXINE <br><br><b>2.- Seleccione los
																				servidores a implementar</b><br> Haz clic en el
																				recuadro del nombre para seleccionar los servidores
																				que deseas implementar, algunos tienen opciones
																				adicionales de filtrado. <br><br><b>3.- Modifica los
																							par&aacutemetros individuales</b><br> Haz clic en
																							el nombre del servidor para que aparezcan los
																							par&aacutemetros del servidor en el recuadro
																							derecho, para m&aacutes informaci&oacuten sobre
																							los par&aacutemetros individuales visita la
																							p&aacutegina de cada programa (<a
																							href="servers.php">Servidores</a>). <br><br><b>4.-
																										Env&iacutee el trabajo</b><br> Una vez
																										terminada la preparaci&oacuten de la solicitud
																										pasamos a dar clic en el bot&oacuten
																										&quot;Submit&quot; en la parte inferior del
																										recuadro izquierdo. 
		
		</div>

	</div>

	<div id="footer">
		Instituto Tecnol&oacutegico de La Paz<br> Centro de Investigaciones
			Biol&oacutegicas del Noroeste, S.C. 
	
	</div>

</body>
</html>
