<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Jozef Liška <jozoliska@gmail.com> 
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductParameterRepository")
 * @ORM\Table(name="product_parameter", uniqueConstraints={@ORM\UniqueConstraint(name="product_parameter_unique", columns={"product_id", "parameter_id"})})
 */
class ProductParameter
{

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var Product
	 * @ORM\ManyToOne(targetEntity="Product")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $product;

	/**
	 * @var Parameter
	 * @ORM\ManyToOne(targetEntity="Parameter")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $parameter;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $valueString;

	/**
	 * @var float
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $valueFloat;

	/**
	 * @var boolean
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $valueBoolean;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return Product
	 */
	public function getProduct() {
		return $this->product;
	}

	/**
	 * @param Product $product
	 */
	public function setProduct(Product $product) {
		$this->product = $product;
	}

	/**
	 * @return Parameter
	 */
	public function getParameter() {
		return $this->parameter;
	}

	/**
	 * @param Parameter $parameter
	 */
	public function setParameter(Parameter $parameter) {
		$this->parameter = $parameter;
	}

	/**
	 * @return string
	 */
	public function getValueString()
	{
		return $this->valueString;
	}

	/**
	 * @param string $valueString
	 * @return self
	 */
	public function setValueString($valueString)
	{
		$this->valueString = $valueString;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getValueFloat()
	{
		return $this->valueFloat;
	}

	/**
	 * @param float $valueFloat
	 * @return self
	 */
	public function setValueFloat($valueFloat)
	{
		$this->valueFloat = $valueFloat;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getValueBoolean()
	{
		return $this->valueBoolean;
	}

	/**
	 * @param boolean $valueBoolean
	 * @return self
	 */
	public function setValueBoolean($valueBoolean)
	{
		$this->valueBoolean = $valueBoolean;
		return $this;
	}
}