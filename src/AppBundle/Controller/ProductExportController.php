<?php

namespace AppBundle\Controller;

use AppBundle\Facade\ProductFacade;
use AppBundle\Repository\ProductParameterRepository;
use AppBundle\Repository\ProductCategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * @author Jozef Liška <jozoliska@gmail.com>
 * @Route(service="app.controller.product_export_controller")
 */
class ProductExportController
{
	const ES_INDEX_URL = 'http://sandbox-cluster-2194566853.eu-west-1.bonsaisearch.net/codecampfilter/';
	const ES_USERNAME = 'h80n7saww1';
	const ES_PASSWORD = 'kkvxaaia0h';
	const ES_MAPPING = ' {
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
						   "type": "nested",
							"properties": {
							   "id": {
								  "type": "long"
							   },
							   "valueString": {
								  "type": "string"
							   },
							   "valueFloat": {
								  "type": "long"
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
			 }
		 }
	 }';

	private $productFacade;
	private $productParameterRepository;
	private $productCategoryRepository;

	public function __construct(ProductFacade $productFacade, ProductParameterRepository $productParameterRepository, ProductCategoryRepository $productCategoryRepository)
	{
		$this->productFacade = $productFacade;
		$this->productParameterRepository = $productParameterRepository;
		$this->productCategoryRepository = $productCategoryRepository;
	}

	private function httpRequest($url, $data = '', $method = false, $headers = ['Content-Type: application/json'])
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERPWD, self::ES_USERNAME . ":" . self::ES_PASSWORD);

		if ($method) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		} else {
			curl_setopt($ch, CURLOPT_POST, 1);
		}

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	/**
	 * @Route("/product-export/{overwrite}", name="product_export", defaults={"overwrite": 0})
	 */
	public function exportAction($overwrite)
	{
		if ($overwrite) {
			// ES - remove index
			$this->httpRequest(self::ES_INDEX_URL, '', 'DELETE');

			// ES - create index + mapping
			$this->httpRequest(self::ES_INDEX_URL, self::ES_MAPPING);
		}

		$rawData = $this->productFacade->getAll(100000, 0);
		$data = [];
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
				$this->httpRequest(self::ES_INDEX_URL . 'product/' . $product->id, json_encode($product));
			}

			$data[] = $product;
		}

		return new JsonResponse($data);
	}
}
