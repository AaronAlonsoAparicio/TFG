<?php
class Usuario {
    private $id;
    private $nombre;
    private $apellidos;
    private $correo;
    private $password;
    private $puntos;
    private $resenas;
    private $vip;

    public function __construct($id = null, $nombre = null, $apellidos = null, $correo = null, $password = null, $puntos = 0, $resenas = null, $vip = false) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->correo = $correo;
        $this->password = $password;
        $this->puntos = $puntos;
        $this->resenas = $resenas;
        $this->vip = $vip;
    }

    // ---------- GETTERS ----------
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getApellidos() {
        return $this->apellidos;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getPuntos() {
        return $this->puntos;
    }

    public function getResenas() {
        return $this->resenas;
    }

    public function getVip() {
        return $this->vip;
    }

    // ---------- SETTERS ----------
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
        return $this;
    }

    public function setApellidos($apellidos) {
        $this->apellidos = $apellidos;
        return $this;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function setPuntos($puntos) {
        $this->puntos = $puntos;
        return $this;
    }

    public function setResenas($resenas) {
        $this->resenas = $resenas;
        return $this;
    }

    public function setVip($vip) {
        $this->vip = $vip;
        return $this;
    }
}
?>
