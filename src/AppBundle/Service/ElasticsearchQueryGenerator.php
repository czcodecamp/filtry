<?php

namespace AppBundle\Service;

/**
 * @author Jozef Liška <jozoliska@gmail.com>
 */
class ElasticsearchQueryGenerator {

	/**
	 * @var integer
	 */
	private $filterCategory = null;

	/**
	 * @var array
	 */
	private $filtersYesno = [];

	/**
	 * @var array
	 */
	private $filtersRange = [];

	/**
	 * @var array
	 */
	private $filtersMultiselect = [];

	/**
	 * @var array
	 */
	private $querySkeleton = [
		'size' => 0,
		'query' => [
			'bool' => [
				'must' => []
			]
		],
		'aggs' => [
			'parameters' => [
				'nested' => [
					'path' => 'parameters'
				],
				'aggs' => [
					'parameter_id' => [
						'terms' => [
							'field' => 'parameters.id',
							'size' => 0
						],
						'aggs' => [
							'parameter_value_string' => [
								'terms' => [
									'field' => 'parameters.value.valueString',
									'size' => 0
								]
							],
							'parameter_value_boolean' => [
								'terms' => [
									'field' => 'parameters.value.valueBoolean',
									'size' => 0
								]
							],
							'parameter_value_float' => [
								'terms' => [
									'field' => 'parameters.value.valueFloat',
									'size' => 0
								]
							],
							'parameter_value_float_min' => [
								'min' => [
									'field' => 'parameters.value.valueFloat'
								]
							],
							'parameter_value_float_max' => [
								'max' => [
									'field' => 'parameters.value.valueFloat'
								]
							]
						]
					]
				]
			]
		]
	];

	public function setFilterCategory($categoryId) {
		$this->filterCategory = (int)$categoryId;
	}

	public function addFilterYesno($parameterId, $value) {
		$this->filtersYesno[(int)$parameterId] = (boolean)$value;
	}

	public function addFilterRange($parameterId, $from = null, $to = null) {
		$this->filtersRange[(int)$parameterId] = [
			'from' => is_null($from) ? null : (float)$from,
			'to' => is_null($to) ? null : (float)$to,
		];
	}

	public function addFilterMultiselect($parameterId, array $values) {
		$this->filtersMultiselect[(int)$parameterId] = $values;
	}

	public function reset() {
		$this->filterCategory = null;
		$this->filtersYesno = [];
		$this->filtersRange = [];
		$this->filtersMultiselect = [];
	}

	private function getBoolQueryCategory() {
		return [
			'nested' => [
				'path' => 'categories',
				'query' => [
					'bool' => [
						'must' => [
							[
								'match' => [
									'categories.id' => [
										'query' => $this->filterCategory,
										'type' => 'phrase'
									]
								]
							]
						]
					]
				]
			]
		];
	}

	private function getBoolQueryYesno() {
		$query = [];

		foreach ($this->filtersYesno as $parameterId => $value) {
			$query[] = [
				'nested' => [
					'path' => 'parameters',
					'query' => [
						'bool' => [
							'must' => [
								[
									'match' => [
										'parameters.id' => $parameterId
									]
								],
								[
									'match' => [
										'parameters.value.valueBoolean' => $value
									]
								]
							]
						]
					]
				]
			];
		}

		return $query;
	}

	private function getBoolQueryRange() {
		$query = [];

		foreach ($this->filtersRange as $parameterId => $value) {
			$query[] = [
				'nested' => [
					'path' => 'parameters',
					'query' => [
						'bool' => [
							'must' => [
								[
									'match' => [
										'parameters.id' => $parameterId
									]
								],
								[
									'range' => [
										'parameters.value.valueFloat' => [
											'gte' => $value['from'],
											'lte' => $value['to']
										]
									]
								]
							]
						]
					]
				]
			];
		}

		return $query;
	}

	private function getBoolQueryMultiselect() {
		$query = [];

		foreach ($this->filtersMultiselect as $parameterId => $values) {
			$subQuery = [
				'bool' => [
					'should' => []
				]
			];

			foreach ($values as $value) {
				$subQuery['bool']['should'][] = [
					'nested' => [
						'path' => 'parameters',
						'query' => [
							'bool' => [
								'must' => [
									[
										'match' => [
											'parameters.id' => $parameterId
										]
									],
									[
										'match' => [
											'parameters.value.valueString' => $value
										]
									]
								]
							]
						]
					]
				];
			}

			$query[] = $subQuery;
		}

		return $query;
	}

	public function generateParametersAggregationQuery() {
		$query = $this->querySkeleton; // copy of skeleton

		if (!is_null($this->filterCategory)) {
			$query['query']['bool']['must'][] = $this->getBoolQueryCategory();
		}

		if (count($this->filtersYesno) > 0) {
			$query['query']['bool']['must'][] = $this->getBoolQueryYesno();
		}

		if (count($this->filtersRange) > 0) {
			$query['query']['bool']['must'][] = $this->getBoolQueryRange();
		}

		if (count($this->filtersMultiselect) > 0) {
			$query['query']['bool']['must'][] = $this->getBoolQueryMultiselect();
		}

		return json_encode($query);
	}
}