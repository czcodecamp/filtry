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

	public function generateParametersAggregationQuery() {
		return json_encode([
			'cat' => $this->filterCategory,
			'yesno' => $this->filtersYesno,
			'range' => $this->filtersRange,
			'multiselect' => $this->filtersMultiselect,
		]);
	}
}