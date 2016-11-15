<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Jozef Liška <jozoliska@gmail.com>
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ParameterRepository")
 */
class Parameter
{

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=150)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(type="string", columnDefinition="ENUM('string', 'float', 'boolean')", nullable=false)
	 */
	private $dataType;

	/**
	 * @var string
	 * @ORM\Column(type="string", columnDefinition="ENUM('multiselect', 'yesno', 'range')", nullable=false)
	 */
	private $filterType;

	/**
	 * @var string
	 * @ORM\Column(type="integer")
	 */
	private $priority;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType()
	{
		return $this->dataType;
	}

	/**
	 * @param string $dataType
	 * @return self
	 */
	public function setDataType($dataType)
	{
		$this->dataType = $dataType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFilterType()
	{
		return $this->filterType;
	}

	/**
	 * @param string $filterType
	 * @return self
	 */
	public function setFilterType($filterType)
	{
		$this->filterType = $filterType;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param int $priority
	 * @return self
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
		return $this;
	}
}