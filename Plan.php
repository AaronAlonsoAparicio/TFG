<?php

class Plan{
 private $id_plan;
 private $cif_empresa;
 private $nombre_empresa;
 private $valoracion_usuarios;
 private $descripcion;
 private $plazas_disponibles;
 



 public function __constructor($id_plan,$cif_empresa, $nombre_empresa,$valoracion_usuarios,$descripcion,$plazas_disponibles){
    $this->id_plan = $id_plan;
    $this->cif_empresa = $cif_empresa;
    $this->nombre_empresa = $nombre_empresa;
    $this->valoracion_usuarios = $valoracion_usuarios;
    $this->descripcion = $descripcion;
    $this->plazas_disponibles = $plazas_disponibles;

 }


}


?>