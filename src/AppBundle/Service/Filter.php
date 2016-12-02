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
		
                if (empty($filterParams)) {
			$data['query'] = $dataQuery;
			$data['filter'] = $dataFilter;
			return $data;  
                }

                $filters = explode(';', $filterParams);
                
                foreach ($filters as $k => $filter) {
                        list($paramId, $values) = explode(':', $filter);
                        // vyhledam v db co je to za param...
			$param = $this->parameterRepository->find($paramId);
			if(!$param) {
				throw new \Exception;
			}
			if($param->getFilterType() == 'multiselect') {
				$values = explode(',', $values);
			} elseif($param->getFilterType() == 'range') {
				$values = explode(',', $values);
				$values[0] = $values[0] ?? '';
				$values[1] = $values[1] ?? '';
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
	/**
	 * 
	 * @param array $values
	 */
	public function createLinkParam($values) {
		$string = '';
		foreach ($values as $k => $val) {
			$string .= $k . ':';
			if(!is_array($val)) {
				$string .= $val . ';'; 
				continue;
			}
			foreach ($val as $key => $value) {
				$string .= $value . ','; 
			}
			$string = rtrim($string, ',');
			$string .= ';';
		}
		$string = $this->removeEmptyParam($string);
		$string = rtrim($string, ';');
		return $string;
	}
	
	/**
	 * Odstraní prázné hodnoty z linku
	 * @param string $filterParams
	 */
	private function removeEmptyParam($filterParams) {
		$patterns = '/[0-9]{1,}:;/';
		$replacements = '';
		return preg_replace($patterns, $replacements, $filterParams);
	}
}