<?php

class Recomendacion {
    private $id_recomendacion;
    private $id_usuario;
    private $id_plan;
    private $texto;
    private $valoracion;
    private $fecha_recomendacion;

    public function __construct($id_recomendacion = null, $id_usuario = null, $id_plan = null, $texto = null, $valoracion = null, $fecha_recomendacion = null) {
        $this->id_recomendacion = $id_recomendacion;
        $this->id_usuario = $id_usuario;
        $this->id_plan = $id_plan;
        $this->texto = $texto;
        $this->valoracion = $valoracion;
        $this->fecha_recomendacion = $fecha_recomendacion;
    }

    // ---------- GETTERS ----------
    public function getIdRecomendacion() {
        return $this->id_recomendacion;
    }

    public function getIdUsuario() {
        return $this->id_usuario;
    }

    public function getIdPlan() {
        return $this->id_plan;
    }

    public function getTexto() {
        return $this->texto;
    }

    public function getValoracion() {
        return $this->valoracion;
    }

    public function getFechaRecomendacion() {
        return $this->fecha_recomendacion;
    }

    // ---------- SETTERS ----------
    public function setIdRecomendacion($id_recomendacion) {
        $this->id_recomendacion = $id_recomendacion;
        return $this;
    }

    public function setIdUsuario($id_usuario) {
        $this->id_usuario = $id_usuario;
        return $this;
    }

    public function setIdPlan($id_plan) {
        $this->id_plan = $id_plan;
        return $this;
    }

    public function setTexto($texto) {
        $this->texto = $texto;
        return $this;
    }

    public function setValoracion($valoracion) {
        $this->valoracion = $valoracion;
        re
