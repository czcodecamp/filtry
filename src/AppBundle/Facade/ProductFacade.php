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
		$query = $this->createFilterCondition($filter, $query);
		
		$query->setFirstResult($offset)
			->setMaxResults($limit)
			->groupBy('p');
		return $query->getQuery()->getResult();
	}	

	public function getCountByCategory(Category $category, $filter) {
		$query =  $this->productRepository->findByCategory($category)
			->select('COUNT(p.id)');
		$query = $this->createFilterCondition($filter, $query);
		return $query->getQuery()->getSingleScalarResult();
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
	
	public function getAllByFilter($limit, $offset, $filter) {
		if(empty($filter)) {
			return $this->getAll($limit, $offset);
		}
		$query = $this->productRepository->getAll();
		$query = $this->createFilterCondition($filter, $query);	
		
		$query->setFirstResult($offset)
			->setMaxResults($limit);
			
		$query->orderBy('p.rank', 'desc');
		$query->groupBy('p');
		return $query->getQuery()->getResult();
	}
	
	public function countAllProducts($filter) {
		$query = $this->productRepository->getAll();
		$query->select('COUNT(p.id)');
		
		$query = $this->createFilterCondition($filter, $query);
		return $query->getQuery()->getSingleScalarResult();		
	}

	private function createFilterCondition($filter, $query) {
		foreach ($filter as $k => $v) {
			$param = $v['param'];
			$values = $v['values']; 

			$query->join('AppBundle\Entity\ProductParameter', 'pp'.$k.'', 'WITH', 'p = pp'.$k.'.product')
			->join('AppBundle\Entity\Parameter', 'param'.$k.'', 'WITH', 'param'.$k.' = pp'.$k.'.parameter');
			
			$query->andWhere('param'.$k.' = :param'.$k)
				->setParameter('param'.$k.'', $param);
			
			$dataType = ucfirst($param->getDataType());
			if($param->getFilterType() == 'multiselect') {	
				$query->andWhere('pp'.$k.'.value'.$dataType.' IN (:values'.$k.')')
				->setParameter('values'.$k.'', $values);			
			} elseif($param->getFilterType() == 'yesno') {
				$query->andWhere('pp'.$k.'.value'.$dataType.' = (:values'.$k.')')
				->setParameter('values'.$k, (int)$values);			
			} elseif($param->getFilterType() == 'range') {
				$query->andWhere('pp'.$k.'.value'.$dataType.' >= :min'.$k.' and pp'.$k.'.value'.$dataType.' <= :max'.$k)
				->setParameter('min'.$k, $values[0])
				->setParameter('max'.$k, $values[1]);			
			}	
		}
		return $query;
	}

}