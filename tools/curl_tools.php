<?php
/*
 * curl_tools.php REVXINE system, curl tools of REVXINE Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
function waitResult($http, $status_patron, $sleep_min, $sleep_max) {
  do {
    $url = $http ['url'];
    unset ( $http );
    sleep ( mt_rand ( $sleep_min, $sleep_max ) );
    $http = curl_request ( $url );
  } while ( preg_match ( $status_patron, $http ['html'] ) );
  
  return $http;
}
function curl_sendPost($url, $post, $boundary) {
  $ch = curl_init ( $url );
  $options = array (
      CURLOPT_HEADER => false,
      CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => array (
          "Content-Type: multipart/form-data; boundary=$boundary" 
      ),
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $post,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_RETURNTRANSFER => true 
  );
  
  curl_setopt_array ( $ch, $options );
  $html = curl_exec ( $ch );
  
  $httpResult = curl_getinfo ( $ch );
  
  curl_error ( $ch );
  curl_close ( $ch );
  
  $http = array (
      'url' => $httpResult ['url'],
      'html' => $html 
  );
  
  return $http;
}
function curl_request($url) {
  $ch = curl_init ( $url );
  $options = array (
      CURLOPT_HEADER => false,
      CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true 
  );
  curl_setopt_array ( $ch, $options );
  $html = curl_exec ( $ch );
  
  $httpResult = curl_getinfo ( $ch );
  
  curl_error ( $ch );
  curl_close ( $ch );
  
  $http = array (
      'url' => $httpResult ['url'],
      'html' => $html 
  );
  
  return $http;
}
?>