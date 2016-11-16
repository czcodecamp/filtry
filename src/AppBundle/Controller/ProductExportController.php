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
	private $productFacade;
	private $productParameterRepository;
	private $productCategoryRepository;

	public function __construct(ProductFacade $productFacade, ProductParameterRepository $productParameterRepository, ProductCategoryRepository $productCategoryRepository)
	{
		$this->productFacade = $productFacade;
		$this->productParameterRepository = $productParameterRepository;
		$this->productCategoryRepository = $productCategoryRepository;
	}

	private function httpPost($url, $data, $username = false, $password = false, $headers = ['Content-Type: application/json'])
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);

		if ($username && $password) {
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
		}

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	/**
	 * @Route("/product-export", name="product_export")
	 */
	public function exportAction()
	{
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

			// add to elastic (1.server)
//			$url = 'http://sandbox-cluster-2194566853.eu-west-1.bonsaisearch.net/codecampfilter/product/'. $product->id;
//			$username = 'h80n7saww1';
//			$password = 'kkvxaaia0h';
//			$this->httpPost($url, json_encode($product), $username, $password);

			// add to elastic (2.server)
//			$url = 'https://bifur-eu-west-1.searchly.com/codecampfilter/product/'. $product->id;
//			$username = 'site';
//			$password = 'd45bde9b5bae8ac8c92c98951104dd4b';
//			$this->httpPost($url, json_encode($product), $username, $password);

			$data[] = $product;
		}

		return new JsonResponse($data);
	}
}
