<?php

namespace AppSistema\Interfaces;

class Enlace
{
    private $nombre;

    private $enlace;

    private $roles;

    private $mifIcon;

    private $enlaceAgregarNuevo;

    private $submodulos;

    private $bloqueado;

    private $mostrarEnPagina;

    private $titulo;

    private $esModulo;

    public function __construct(string $enlace, string $nombre, string $mifIcon = 'mif-apps', string $enlaceAgregarNuevo = null, array $roles = [], array $submodulos = [])
    {
        $this->nombre = $nombre;
        $this->enlace = $enlace;
        $this->roles = $roles;
        $this->mifIcon = $mifIcon;
        $this->enlaceAgregarNuevo = $enlaceAgregarNuevo;
        $this->submodulos = $submodulos;
        $this->esModulo = false;
        $this->bloqueado = false;
        $this->mostrarEnPagina = false;
    }


    /**
     * @return bool
     */
    public function mostrarEnPagina(): bool
    {
        return $this->mostrarEnPagina;
    }

    /**
     * @return string
     */
    public function getEnlace(): string
    {
        return $this->enlace;
    }

    /**
     * @return bool
     */
    public function isBloqueado(): bool
    {
        return $this->bloqueado;
    }

    /**
     * @return string
     */
    public function getMifIcon(): string
    {
        return $this->mifIcon;
    }

    /**
     * @return string
     */
    public function getNombre(): string
    {
        return $this->nombre;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getSubmodulos(): array
    {
        return $this->submodulos;
    }

    public function addSubmodulo(ModuloEnlace $enlace): void
    {
        $this->submodulos[] = $enlace;
    }

    /**
     * @param mixed $titulo
     */
    public function setTitulo($titulo): void
    {
        $this->titulo = $titulo;
    }

    /**
     * @return mixed
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * @return string
     */
    public function getEnlaceAgregarNuevo(): ?string
    {
        return $this->enlaceAgregarNuevo;
    }

    /**
     * @return void
     */
    public function setEsModulo(): void
    {
        $this->esModulo = true;
    }

    /**
     * @return void
     */
    public function setMostrarEnPagina(): void
    {
        $this->mostrarEnPagina = true;
    }

    /**
     * @return void
     */
    public function setBloqueado(): void
    {
        $this->bloqueado = true;
    }

    /**
     * @return bool
     */
    public function getEsModulo(): bool
    {
        return $this->esModulo;
    }

}
