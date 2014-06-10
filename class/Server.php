<?php
/*
 * Server.php REVXINE system, Server class Copyright (C) 2014 Juan Luis Salazar Mendoza juanluismdz@gmail.com This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
?>

<?php
include_once ('tools/tools.php');
class Server {
  private $url;
  private $form;
  private $post;
  private $Result_isOk;
  private $Content = array ();
  public function setResult_isOk($isOk) {
    $this->Result_isOk = $isOk;
  }
  public function getResult_isOk() {
    return $this->Result_isOk;
  }
  public function setForm($form) {
    $this->form = $form;
  }
  public function getForm() {
    return $this->form;
    unset ( $this->form );
  }
  public function setUrl($URL) {
    $this->url = $URL;
  }
  public function getUrl() {
    return $this->url;
  }
  public function setPost($post) {
    $this->post = $post;
  }
  public function getPost() {
    return $this->post;
  }
  public function setContent($key, $value) {
    $this->Content [$key] = $value;
  }
  public function getContent() {
    return $this->Content;
  }
}
?>