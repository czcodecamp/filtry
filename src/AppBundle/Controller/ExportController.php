<?php

namespace AppBundle\Controller;

use AppBundle\Facade\ProductFacade;
use AppBundle\Repository\ProductParameterRepository;
use AppBundle\Repository\ProductCategoryRepository;
use AppBundle\Repository\ParameterRepository;
use AppBundle\Service\ElasticsearchConnector;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * @author Jozef Liška <jozoliska@gmail.com>
 * @Route(service="app.controller.export_controller")
 */
class ExportController
{
	const ES_MAPPING = '{
		"mappings" : {
			"product" : {
				"properties": {
					"id": {
						"type": "long"
					},
					"title": {
						"type": "string"
					},
					"description": {
						"type": "string"
					},
					"slug": {
						"type": "string"
					},
					"image": {
						"type": "string"
					},
					"price": {
						"type": "long"
					},
					"rank": {
						"type": "long"
					},
					"parameters": {
						"type": "nested",
						"properties": {
							"id": {
								"type": "long"
							},
							"name": {
								"type": "string"
							},
							"dataType": {
								"type": "string"
							},
							"filterType": {
								"type": "string"
							},
							"priority": {
								"type": "long"
							},
							"value": {
								"properties": {
									"id": {
										"type": "long"
									},
									"valueString": {
										"type": "string",
										"index": "not_analyzed"
									},
									"valueFloat": {
										"type": "double"
									},
									"valueBoolean": {
										"type": "boolean"
									}
								}
							}
						}
					},
					"categories": {
						"type": "nested",
						"properties": {
							"id": {
								"type": "long"
							},
							"title": {
								"type": "string"
							},
							"slug": {
								"type": "string"
							}
						}
					}
				}
			},
			"parameter": {
				"properties": {
					"id": {
						"type": "long"
					},
					"name": {
						"type": "string"
					},
					"dataType": {
						"type": "string"
					},
					"filterType": {
						"type": "string"
					},
					"priority": {
						"type": "long"
					}
				}
			}
		}
	}';

	private $productFacade;
	private $productParameterRepository;
	private $productCategoryRepository;
	private $parameterRepository;
	private $elasticsearchConnector;

	public function __construct(
		ProductFacade $productFacade,
		ProductParameterRepository $productParameterRepository,
		ProductCategoryRepository $productCategoryRepository,
		ParameterRepository $parameterRepository,
		ElasticsearchConnector $elasticsearchConnector
	) {
		$this->productFacade = $productFacade;
		$this->productParameterRepository = $productParameterRepository;
		$this->productCategoryRepository = $productCategoryRepository;
		$this->parameterRepository = $parameterRepository;
		$this->elasticsearchConnector = $elasticsearchConnector;
	}

	private function exportProducts($overwrite)
	{
		$data = [];
		$rawData = $this->productFacade->getAll(100000, 0);

		foreach ($rawData as $rawProduct) {
			$product = new \stdClass();
			$product->id = $rawProduct->getId();
			$product->title = $rawProduct->getTitle();
			$product->image = $rawProduct->getImage();
			$product->slug = $rawProduct->getSlug();
			$product->description = $rawProduct->getDescription();
			$product->price = $rawProduct->getPrice();
			$product->rank = $rawProduct->getRank();

			// parameters
			$product->parameters = [];
			$rawParameters = $this->productParameterRepository->findByProduct($rawProduct);
			foreach ($rawParameters as $rawParameter) {
				$parameter = new \stdClass();
				$parameter->id = $rawParameter->getParameter()->getId();
				$parameter->name = $rawParameter->getParameter()->getName();
				$parameter->dataType = $rawParameter->getParameter()->getDataType();
				$parameter->filterType = $rawParameter->getParameter()->getFilterType();
				$parameter->priority = $rawParameter->getParameter()->getPriority();
				$parameter->value = new \stdClass();
				$parameter->value->id = $rawParameter->getId();
				$parameter->value->valueString = $rawParameter->getValueString();
				$parameter->value->valueFloat = $rawParameter->getValueFloat();
				$parameter->value->valueBoolean = $rawParameter->getValueBoolean();

				$product->parameters[] = $parameter;
			}

			// categories
			$addedCategories = [];
			$product->categories = [];
			$rawCategories = $this->productCategoryRepository->findBy(['product' => $rawProduct]);
			foreach ($rawCategories as $rawCategory) {
				$rc = $rawCategory->getCategory();
				do {
					// check if is first occurrence
					if (in_array($rc->getId(), $addedCategories)) {
						continue;
					}

					$category = new \stdClass();
					$category->id = $rc->getId();
					$category->title = $rc->getTitle();
					$category->slug = $rc->getSlug();

					$product->categories[] = $category;
					$addedCategories[] = $rc->getId();
				} while ($rc = $rc->getParentCategory());
			}

			if ($overwrite) {
				// ES - add product
				$this->elasticsearchConnector->request('codecampfilter/product/' . $product->id, json_encode($product), 'POST');
			}

			$data[] = $product;
		}

		return $data;
	}

	private function exportParameters($overwrite)
	{
		$data = [];
		$rawData = $this->parameterRepository->findAll();

		foreach ($rawData as $rawParameter) {
			$parameter = new \stdClass();
			$parameter->id = $rawParameter->getId();
			$parameter->name = $rawParameter->getName();
			$parameter->dataType = $rawParameter->getDataType();
			$parameter->filterType = $rawParameter->getFilterType();
			$parameter->priority = $rawParameter->getPriority();

			if ($overwrite) {
				// ES - add parameter
				$this->elasticsearchConnector->request('codecampfilter/parameter/' . $parameter->id, json_encode($parameter), 'POST');
			}

			$data[] = $parameter;
		}

		return $data;
	}

	/**
	 * @Route("/export/{overwrite}", name="export", defaults={"overwrite": 0})
	 */
	public function exportAction($overwrite)
	{
		if ($overwrite) {
			// ES - remove index
			$this->elasticsearchConnector->request('codecampfilter', '', 'DELETE');

			// ES - create index + mapping
			$this->elasticsearchConnector->request('codecampfilter', self::ES_MAPPING, 'POST');
		}

		$data = new \stdClass();
		$data->products = $this->exportProducts($overwrite);
		$data->parameters = $this->exportParameters($overwrite);

		return new JsonResponse($data);
	}
}
