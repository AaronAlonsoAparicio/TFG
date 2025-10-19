<?php

class Plan {
    private $id_plan;
    private $cif_empresa;
    private $nombre_empresa;
    private $valoracion_usuarios;
    private $descripcion;
    private $plazas_disponibles;

    
    public function __construct($id_plan = null, $cif_empresa = null, $nombre_empresa = null, $valoracion_usuarios = null, $descripcion = null, $plazas_disponibles = null) {
        $this->id_plan = $id_plan;
        $this->cif_empresa = $cif_empresa;
        $this->nombre_empresa = $nombre_empresa;
        $this->valoracion_usuarios = $valoracion_usuarios;
        $this->descripcion = $descripcion;
        $this->plazas_disponibles = $plazas_disponibles;
    }

    // ---------- GETTERS ----------
    public function getIdPlan() {
        return $this->id_plan;
    }

    public function getCifEmpresa() {
        return $this->cif_empresa;
    }

    public function getNombreEmpresa() {
        return $this->nombre_empresa;
    }

    public function getValoracionUsuarios() {
        return $this->valoracion_usuarios;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getPlazasDisponibles() {
        return $this->plazas_disponibles;
    }

    // ---------- SETTERS ----------
    public function setIdPlan($id_plan) {
        $this->id_plan = $id_plan;
        return $this;
    }

    public function setCifEmpresa($cif_empresa) {
        $this->cif_empresa = $cif_empresa;
        return $this;
    }

    public function setNombreEmpresa($nombre_empresa) {
        $this->nombre_empresa = $nombre_empresa;
        return $this;
    }

    public function setValoracionUsuarios($valoracion_usuarios) {
        $this->valoracion_usuarios = $valoracion_usuarios;
        return $this;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function setPlazasDisponibles($plazas_disponibles) {
        $this->plazas_disponibles = $plazas_disponibles;
        return $this;
    }
}

?>
