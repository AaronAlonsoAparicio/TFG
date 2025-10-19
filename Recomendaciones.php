<?php

class Usuario{
 private $id;
 private $nombre;
 private $apellidos;
 private $password;
 private $puntos;
 private $resenas;
 private $vip;



 public function __constructor($id,$nombre, $apellidos,$password,$puntos,$resenas,$vip){
     $this->id = $id;
    $this->nombre = $nombre;
    $this->apellidos = $apellidos;
    $this->password = $password;
    $this->puntos = $puntos;
    $this->resenas = $resenas;
    $this->vip = $vip;

 }


}


?>