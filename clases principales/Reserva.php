<?php

class Reserva {
    private $id_reserva;
    private $id_usuario;
    private $id_plan;
    private $fecha_reserva;
    private $estado;
    private $precio;


    public function __construct($id_reserva = null, $id_usuario = null, $id_plan = null, $fecha_reserva = null, $estado = null, $precio = null) {
        $this->id_reserva = $id_reserva;
        $this->id_usuario = $id_usuario;
        $this->id_plan = $id_plan;
        $this->fecha_reserva = $fecha_reserva;
        $this->estado = $estado;
        $this->precio = $precio;
    }

    // ---------- GETTERS ----------
    public function getIdReserva() {
        return $this->id_reserva;
    }

    public function getIdUsuario() {
        return $this->id_usuario;
    }

    public function getIdPlan() {
        return $this->id_plan;
    }

    public function getFechaReserva() {
        return $this->fecha_reserva;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getPrecio() {
        return $this->precio;
    }

    // ---------- SETTERS ----------
    public function setIdReserva($id_reserva) {
        $this->id_reserva = $id_reserva;
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

    public function setFechaReserva($fecha_reserva) {
        $this->fecha_reserva = $fecha_reserva;
        return $this;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
        return $this;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
        return $this;
    }
}

?>
