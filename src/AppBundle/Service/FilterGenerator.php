<?php

namespace AppBundle\Service;

use AppBundle\Entity\Parameter;
use AppBundle\Repository\ParameterRepository;
use AppBundle\Service\ElasticsearchQueryGenerator;
use AppBundle\Service\ElasticsearchConnector;

/**
 * @author Jozef Liška <jozoliska@gmail.com>
 */
class FilterGenerator {

    const ES_URI_PRODUCT_SEARCH = 'codecampfilter/product/_search';

	private $parameterRepository;
	private $elasticsearchQueryGenerator;
	private $elasticsearchConnector;
	private $filterCategory = null;
	private $inputFilters = [];

	public function __construct(
	    ParameterRepository $parameterRepository,
        ElasticsearchQueryGenerator $elasticsearchQueryGenerator,
        ElasticsearchConnector $elasticsearchConnector
    ) {
		$this->parameterRepository = $parameterRepository;
		$this->elasticsearchQueryGenerator = $elasticsearchQueryGenerator;
		$this->elasticsearchConnector = $elasticsearchConnector;
	}

	public function setInputFilters($categoryId = null, array $inputFilters = []) {
		$this->filterCategory = $categoryId;
		$this->inputFilters = $inputFilters;
	}

    /**
     * TODO: Replace parameter repository source to Elasticseach
     * @param $parameterId integer
     * @return null|Parameter
     */
	private function getParameter($parameterId) {
	    return $this->parameterRepository->findOneBy(['id' => $parameterId]);
    }

	private function processData($rawData) {
	    $filters = [];
        $totalProducts = $rawData->hits->total;

        foreach ($rawData->aggregations->parameters->parameter_id->buckets as $bucket) {
            // include only filter which has all filtered products
            if ($totalProducts > $bucket->doc_count) {
                continue;
            }

            // get parameter detail
            $parameterId = $bucket->key;
            $parameter = $this->getParameter($parameterId);

            // get value buckets by data type
            switch ($parameter->getDataType()) {
                case 'string':
                    $valueBuckets = $bucket->parameter_value_string->buckets;
                    break;
                case 'float':
                    $valueBuckets = $bucket->parameter_value_float->buckets;
                    break;
                case 'boolean':
                    $valueBuckets = $bucket->parameter_value_boolean->buckets;
                    break;
            }

            // include only filter which has min 2 values (options)
            if (count($valueBuckets) < 2) {
                continue;
            }

            // get filter options
            $options = [];
            foreach ($valueBuckets as $valueBucket) {
                $options[] = [
                    'value' => $valueBucket->key,
                    'count' => $valueBucket->doc_count
                ];
            }

            $filter = [
                'id' => $parameterId,
                'type' => $parameter->getFilterType(),
                'name' => $parameter->getName(),
                'options' => $options
            ];

            // extra metrics for range filter type
            if ($parameter->getFilterType() == 'range') {
                $from = $bucket->parameter_value_float_min->value;
                $to = $bucket->parameter_value_float_max->value;
                $filter['range'] = [
                    'from' => $from,
                    'to' => $to
                ];
            }

            $filters[$parameter->getPriority()] = $filter;
        }

        // sort filters by priority
        ksort($filters);

        // return filter array with reset keys
        return array_values($filters);
    }

	public function generateFilters() {
		$this->elasticsearchQueryGenerator->reset();

		if (!is_null($this->filterCategory)) {
			$this->elasticsearchQueryGenerator->setFilterCategory($this->filterCategory);
		}

		foreach ($this->inputFilters as $type => $filters) {
			foreach ($filters as $parameterId => $value) {
				switch ($type) {
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
		}

		$esQuery = $this->elasticsearchQueryGenerator->generateParametersAggregationQuery();
        $response = $this->elasticsearchConnector->request(self::ES_URI_PRODUCT_SEARCH, $esQuery);
        $rawData = json_decode($response);

		return $this->processData($rawData);
	}
}