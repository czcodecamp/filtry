<?php

namespace AppBundle\Service;

use AppBundle\Entity\Parameter;
use AppBundle\Repository\ParameterRepository;
use AppBundle\Service\ElasticsearchQueryGenerator;

/**
 * @author Jozef Liška <jozoliska@gmail.com>
 */
class FilterGenerator {

	private $parameterRepository;
	private $elasticsearchQueryGenerator;
	private $filterCategory = null;
	private $inputFilters = [];

	public function __construct(ParameterRepository $parameterRepository, ElasticsearchQueryGenerator $elasticsearchQueryGenerator) {
		$this->parameterRepository = $parameterRepository;
		$this->elasticsearchQueryGenerator = $elasticsearchQueryGenerator;
	}

	public function setInputFilters($categoryId = null, array $inputFilters = []) {
		$this->filterCategory = $categoryId;
		$this->inputFilters = $inputFilters;
	}

	public function generateFilters() {
		$this->elasticsearchQueryGenerator->reset();
		$this->elasticsearchQueryGenerator->setFilterCategory($this->filterCategory);

		foreach ($this->inputFilters as $parameterId => $value) {
			/* @var $parameter Parameter */
			$parameter = $this->parameterRepository->findOneBy(['id' => $parameterId]);

			switch ($parameter->getFilterType()) {
				case 'yesno':
					$this->elasticsearchQueryGenerator->addFilterYesno($parameterId, $value);
					break;
				case 'range':
					$this->elasticsearchQueryGenerator->addFilterRange($parameterId, $value[0], $value[1]);
					break;
				case 'multiselect':
					$this->elasticsearchQueryGenerator->addFilterMultiselect($parameterId, $value);
					break;
			}
		}

		$esQuery = $this->elasticsearchQueryGenerator->generateParametersAggregationQuery();

		return $esQuery;
	}
}