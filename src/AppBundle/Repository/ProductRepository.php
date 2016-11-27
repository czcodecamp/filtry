<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 * @author Aleš Kůdela <kudela.ales@gmail.com>
 */
class ProductRepository extends EntityRepository
{

	/**
	 * @param Category $category
	 * @return QueryBuilder
	 */
	public function findByCategory(Category $category)
	{
		$builder = $this->_em->createQueryBuilder()
			->select('p')
			->from('AppBundle\Entity\Product', 'p')
			->join('AppBundle\Entity\ProductCategory', 'pc', 'WITH', 'p = pc.product')
			->join('AppBundle\Entity\Category', 'c', 'WITH', 'pc.category = c')
			->where('c.left >= :lft and c.right <= :rgt')
			->setParameter("lft", $category->getLeft())
			->setParameter("rgt", $category->getRight());
		return $builder;
	}
	
	/**
	 * @param Category $category
	 * @return QueryBuilder
	 */
	public function findByCategoryAndFilter(Category $category)
	{
		$builder = $this->_em->createQueryBuilder()
			->select('p')
			->from('AppBundle\Entity\Product', 'p')
			->join('AppBundle\Entity\ProductCategory', 'pc', 'WITH', 'p = pc.product')
			->join('AppBundle\Entity\Category', 'c', 'WITH', 'pc.category = c')
			
			->leftJoin('AppBundle\Entity\ProductParameter', 'pp', 'WITH', 'p = pp.product')
			->leftJoin('AppBundle\Entity\Parameter', 'param', 'WITH', 'param = pp.parameter')
			
			->where('c.left >= :lft and c.right <= :rgt')
			->setParameter("lft", $category->getLeft())
			->setParameter("rgt", $category->getRight());			
		return $builder;
	}	

	public function countAll() {
		return $this->_em->createQueryBuilder()
			->select('COUNT(p.id)')
			->from('AppBundle\Entity\Product', 'p')
			->getQuery()->getSingleScalarResult();
	}

}
