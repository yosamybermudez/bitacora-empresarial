<?php

namespace AppBase\Entity;

use App\Entity\BaseEntity;
use AppSistema\Entity\Usuario;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ModInventario\Entity\Movimiento;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=OrganizacionRepository::class)
 * @Vich\Uploadable
 */
class Empresa extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siglas;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codigo_reeup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codigo_nit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creadoEn;

    /**
     * @ORM\Column(type="datetime")
     */
    private $actualizadoEn;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $creadoPor;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $actualizadoPor;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $esCliente;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $esProveedor;

    /**
     * @ORM\Column(type="boolean", nullable=true, unique=true)
     */
    private $esMiOrganizacion;

    /**
     * @ORM\OneToMany(targetEntity=Movimiento::class, mappedBy="clienteProveedor")
     */
    private $movimientos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domicilio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefonos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $correosElectronicos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaCupNumero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaCupTitular;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaCupBancoNombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaCupBancoSucursal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaCupBancoDireccion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaMlcNumero;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaMlcTitular;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaMlcBancoNombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaMlcBancoSucursal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaMlcBancoDireccion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contactoPrincipal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @Vich\UploadableField(mapping="organizacion_logo", fileNameProperty="logo")
     * @var File
     */
    private $logoFile;

    public function __construct()
    {
        $this->movimientos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCreadoEn(): ?\DateTimeInterface
    {
        return $this->creadoEn;
    }

    public function setCreadoEn(\DateTimeInterface $creadoEn): self
    {
        $this->creadoEn = $creadoEn;

        return $this;
    }

    public function getActualizadoEn(): ?\DateTimeInterface
    {
        return $this->actualizadoEn;
    }

    public function setActualizadoEn(\DateTimeInterface $actualizadoEn): self
    {
        $this->actualizadoEn = $actualizadoEn;

        return $this;
    }

    public function getCreadoPor(): ?Usuario
    {
        return $this->creadoPor;
    }

    public function setCreadoPor(?Usuario $creadoPor): self
    {
        $this->creadoPor = $creadoPor;

        return $this;
    }

    public function getActualizadoPor(): ?Usuario
    {
        return $this->actualizadoPor;
    }

    public function setActualizadoPor(?Usuario $actualizadoPor): self
    {
        $this->actualizadoPor = $actualizadoPor;

        return $this;
    }

    public function esCliente(): ?bool
    {
        return $this->esCliente;
    }

    public function setEsCliente(?bool $esCliente): self
    {
        $this->esCliente = $esCliente;

        return $this;
    }

    public function esProveedor(): ?bool
    {
        return $this->esProveedor;
    }

    public function setEsProveedor(?bool $esProveedor): self
    {
        $this->esProveedor = $esProveedor;

        return $this;
    }

    public function getMovimientos(): Collection
    {
        return $this->movimientos;
    }

    public function addMovimiento(Movimiento $movimiento): self
    {
        if (!$this->movimientos->contains($movimiento)) {
            $this->movimientos[] = $movimiento;
            $movimiento->setEmpresa($this);
        }

        return $this;
    }

    public function removeMovimiento(Movimiento $movimiento): self
    {
        if ($this->movimientos->removeElement($movimiento)) {
            // set the owning side to null (unless already changed)
            if ($movimiento->getEmpresa() === $this) {
                $movimiento->setEmpresa(null);
            }
        }

        return $this;
    }

    public function getDomicilio(): ?string
    {
        return $this->domicilio;
    }

    public function setDomicilio(?string $domicilio): self
    {
        $this->domicilio = $domicilio;

        return $this;
    }

    public function getTelefonos(): ?string
    {
        return $this->telefonos;
    }

    public function setTelefonos(?string $telefonos): self
    {
        $this->telefonos = $telefonos;

        return $this;
    }

    public function isEsCliente(): ?bool
    {
        return $this->esCliente;
    }

    public function isEsProveedor(): ?bool
    {
        return $this->esProveedor;
    }


    public function getCorreosElectronicos(): ?string
    {
        return $this->correosElectronicos;
    }

    public function setCorreosElectronicos(?string $correosElectronicos): self
    {
        $this->correosElectronicos = $correosElectronicos;

        return $this;
    }

    public function getCuentaCupNumero(): ?string
    {
        return $this->cuentaCupNumero;
    }

    public function setCuentaCupNumero(?string $cuentaCupNumero): self
    {
        $this->cuentaCupNumero = $cuentaCupNumero;

        return $this;
    }

    public function getCuentaCupTitular(): ?string
    {
        return $this->cuentaCupTitular;
    }

    public function setCuentaCupTitular(?string $cuentaCupTitular): self
    {
        $this->cuentaCupTitular = $cuentaCupTitular;

        return $this;
    }

    public function getCuentaCupBancoNombre(): ?string
    {
        return $this->cuentaCupBancoNombre;
    }

    public function setCuentaCupBancoNombre(?string $cuentaCupBancoNombre): self
    {
        $this->cuentaCupBancoNombre = $cuentaCupBancoNombre;

        return $this;
    }

    public function getCuentaCupBancoSucursal(): ?string
    {
        return $this->cuentaCupBancoSucursal;
    }

    public function setCuentaCupBancoSucursal(?string $cuentaCupBancoSucursal): self
    {
        $this->cuentaCupBancoSucursal = $cuentaCupBancoSucursal;

        return $this;
    }

    public function getCuentaCupBancoDireccion(): ?string
    {
        return $this->cuentaCupBancoDireccion;
    }

    public function setCuentaCupBancoDireccion(?string $cuentaCupBancoDireccion): self
    {
        $this->cuentaCupBancoDireccion = $cuentaCupBancoDireccion;

        return $this;
    }

    public function getCuentaMlcNumero(): ?string
    {
        return $this->cuentaMlcNumero;
    }

    public function setCuentaMlcNumero(?string $cuentaMlcNumero): self
    {
        $this->cuentaMlcNumero = $cuentaMlcNumero;

        return $this;
    }

    public function getCuentaMlcTitular(): ?string
    {
        return $this->cuentaMlcTitular;
    }

    public function setCuentaMlcTitular(?string $cuentaMlcTitular): self
    {
        $this->cuentaMlcTitular = $cuentaMlcTitular;

        return $this;
    }

    public function getCuentaMlcBancoNombre(): ?string
    {
        return $this->cuentaMlcBancoNombre;
    }

    public function setCuentaMlcBancoNombre(?string $cuentaMlcBancoNombre): self
    {
        $this->cuentaMlcBancoNombre = $cuentaMlcBancoNombre;

        return $this;
    }

    public function getCuentaMlcBancoSucursal(): ?string
    {
        return $this->cuentaMlcBancoSucursal;
    }

    public function setCuentaMlcBancoSucursal(?string $cuentaMlcBancoSucursal): self
    {
        $this->cuentaMlcBancoSucursal = $cuentaMlcBancoSucursal;

        return $this;
    }

    public function getCuentaMlcBancoDireccion(): ?string
    {
        return $this->cuentaMlcBancoDireccion;
    }

    public function setCuentaMlcBancoDireccion(?string $cuentaMlcBancoDireccion): self
    {
        $this->cuentaMlcBancoDireccion = $cuentaMlcBancoDireccion;

        return $this;
    }

    public function getCuentaCup(): ?string {
        $datos = null;
        if($this->cuentaCupTitular && $this->cuentaCupTitular !== ''){
            $datos .= $this->cuentaCupTitular;
            if($this->cuentaCupNumero && $this->cuentaCupNumero !== '') {
                $datos .= "- No. " . $this->cuentaCupNumero;
            }
        }
        if($this->cuentaCupBancoNombre && $this->cuentaCupBancoNombre !== ''){
            $datos .= " / " . $this->cuentaCupBancoNombre;
            if($this->cuentaCupBancoSucursal && $this->cuentaCupBancoSucursal !== '') {
                $datos .= ", " . $this->cuentaCupBancoSucursal;
            }
        }
        return $datos;
    }

    public function getCuentaMlc(): ?string {
        $datos = null;
        if($this->cuentaMlcTitular && $this->cuentaMlcTitular !== ''){
            $datos .= $this->cuentaMlcTitular;
            if($this->cuentaMlcNumero && $this->cuentaMlcNumero !== '') {
                $datos .= "- No. " . $this->cuentaMlcNumero;
            }
        }
        if($this->cuentaMlcBancoNombre && $this->cuentaMlcBancoNombre !== ''){
            $datos .= " / " . $this->cuentaMlcBancoNombre;
            if($this->cuentaMlcBancoSucursal && $this->cuentaMlcBancoSucursal !== '') {
                $datos .= ", " . $this->cuentaMlcBancoSucursal;
            }
        }
        return $datos;
    }

    public function isEsMiOrganizacion(): ?bool
    {
        return $this->esMiOrganizacion;
    }

    public function setEsMiOrganizacion(?bool $esMiOrganizacion): self
    {
        $this->esMiOrganizacion = $esMiOrganizacion;

        return $this;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(?string $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getContactoPrincipal(): ?string
    {
        return $this->contactoPrincipal;
    }

    public function setContactoPrincipal(?string $contactoPrincipal): self
    {
        $this->contactoPrincipal = $contactoPrincipal;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return File
     */
    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    /**
     * @param File $logoFile
     */
    public function setLogoFile(File $logoFile = null): void
    {
        $this->logoFile = $logoFile;
        if ($logoFile) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->actualizadoEn = new \DateTime();
        }
    }

    public function getSiglas(): ?string
    {
        return $this->siglas;
    }

    public function setSiglas(?string $siglas): self
    {
        $this->siglas = $siglas;

        return $this;
    }

    public function getCodigoReeup(): ?string
    {
        return $this->codigo_reeup;
    }

    public function setCodigoReeup(?string $codigo_reeup): self
    {
        $this->codigo_reeup = $codigo_reeup;

        return $this;
    }

    public function getCodigoNit(): ?string
    {
        return $this->codigo_nit;
    }

    public function setCodigoNit(?string $codigo_nit): self
    {
        $this->codigo_nit = $codigo_nit;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id',
            'creadoEn',
            'actualizadoEn',
            'creadoPor',
            'actualizadoPor'
        ];
        return $this->toJsonAttributes($attributes);
    }
}
