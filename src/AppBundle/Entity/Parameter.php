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
	 * @ORM\Column(type="smallint", options={"comment": "Options: 1=string, 2=float, 3=boolean"})
	 */
	private $type;

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
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param int $type
	 * @return self
	 */
	public function setType($type)
	{
		$this->type = $type;
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