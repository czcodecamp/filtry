<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Product;

/**
 * @author Jozef Liška <jozoliska@gmail.com>
 */
class ProductParameterRepository extends EntityRepository
{
	public function findByProduct(Product $product)
	{
		return $this->_em->createQueryBuilder()
			->select('pp')
			->from('AppBundle\Entity\ProductParameter', 'pp')
			->join('AppBundle\Entity\Parameter', 'p', 'WITH', 'pp.parameter = p')
			->where('pp.product = :productId')
			->setParameter("productId", $product->getId())
			->orderBy('p.priority', 'asc')
			->getQuery()
			->getResult();
	}
}
