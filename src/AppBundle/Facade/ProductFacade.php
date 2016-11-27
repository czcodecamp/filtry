<?php

namespace AppBundle\Facade;

use AppBundle\Entity\Category;
use AppBundle\Repository\ProductRepository;
use \Helpers\Parameter\FilterType;

/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 * @author Aleš Kůdela <kudela.ales@gmail.com>
 */
class ProductFacade {

	private $productRepository;

	public function __construct(ProductRepository $productRepository) {
		$this->productRepository = $productRepository;
	}

	public function findByCategory(Category $category, $limit, $offset) {
		return $this->productRepository->findByCategory($category)
			->setFirstResult($offset)
			->setMaxResults($limit)
			->getQuery()->getResult();
	}
	
	public function findByCategoryAndFilter(Category $category, $filter, $limit, $offset) {
		$query =  $this->productRepository->findByCategory($category);
		
		foreach ($filter as $k => $v) {
			$param = $v['param'];
			$values = $v['values']; 
						
			$query->leftJoin('AppBundle\Entity\ProductParameter', 'pp'.$k.'', 'WITH', 'p = pp'.$k.'.product')
			->leftJoin('AppBundle\Entity\Parameter', 'param'.$k.'', 'WITH', 'param'.$k.' = pp'.$k.'.parameter');
			
			$dataType = ucfirst($param->getDataType());
			if($param->getFilterType() == 'multiselect') {
				$values = explode(',', $values);	
				$query->andWhere('pp'.$k.'.value'.$dataType.' IN (:values'.$k.')')
				->setParameter('values'.$k.'', $values);			
			} elseif($param->getFilterType() == 'yesno') {
				$query->andWhere('pp'.$k.'.value'.$dataType.' = (:values'.$k.')')
				->setParameter('values'.$k, (int)$values);			
			} elseif($param->getFilterType() == 'range') {
				$values = explode(',', $values);
				$query->andWhere('pp'.$k.'.value'.$dataType.' >= :min'.$k.' and pp'.$k.'.value'.$dataType.' <= :max'.$k)
				->setParameter('min'.$k, $values[0])
				->setParameter('max'.$k, $values[1]);			
			}	
		}
			$query->setFirstResult($offset)
			->setMaxResults($limit)->groupBy('p');
		return $query->getQuery()->getResult();
	}	

	public function getCountByCategory(Category $category) {
		return $this->productRepository->findByCategory($category)
			->select('COUNT(p.id)')
			->getQuery()->getSingleScalarResult();
	}

	public function getBySlug($slug) {
		return $this->productRepository->findOneBy([
			"slug" => $slug,
		]);
	}

	public function getAll($limit, $offset) {
		return $this->productRepository->findBy(
			[],
			[
				"rank" => "desc"
			],
			$limit,
			$offset
		);
	}

	public function countAllProducts() {
		return $this->productRepository->countAll();
	}


}