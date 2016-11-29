<?php

namespace AppBundle\Service;

use AppBundle\Repository\ProductParameterRepository;
use AppBundle\Repository\ParameterRepository;
/**
 * @author Aleš Kůdela <kudela.ales@gmail.com>
 */
class Filter {
	
	private $productParameterRepository;
	private $parameterRepository;
	
	function __construct(ProductParameterRepository $productParameterRepository,ParameterRepository $parameterRepository) {
		$this->productParameterRepository = $productParameterRepository;
		$this->parameterRepository = $parameterRepository;
	}

	/**
	 * @param type $filterParams
	 * @return array
	 * @throws \Exception
	 */
        public function prepareParams($filterParams) {
		$dataQuery = array();
		$dataFilter = array();
		
                if (strpos($filterParams, 'f:') === false) {
			$data['query'] = $dataQuery;
			$data['filter'] = $dataFilter;
			return $data;  
                }

                $string = explode('f:', $filterParams);
                $filters = explode(';', $string[1]);
                
                foreach ($filters as $k => $filter) {
                        list($paramId, $values) = explode(':', $filter);
                        // vyhledam v db co je to za param...
			$param = $this->parameterRepository->find($paramId);
			if(!$param) {
				throw new \Exception;
			}
			if($param->getFilterType() == 'multiselect' || 
				$param->getFilterType() == 'range') {
				$values = explode(',', $values);
			}
                        // ulozim do pole dle typu
                        $dataQuery[$paramId]['param'] = $param;
                        $dataQuery[$paramId]['values'] = $values;
                        $dataFilter[$param->getFilterType()][$paramId] = $values;
                }      
		$data['query'] = $dataQuery;
		$data['filter'] = $dataFilter;
		
		return $data;                   
        }
}