<?php

namespace AppBundle\Controller;

use AppBundle\Facade\ProductFacade;
use AppBundle\Repository\ProductParameterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 * @Route(service="app.controller.product_controller")
 */
class ProductController
{
	private $productFacade;
	private $productParameterRepository;

	public function __construct(ProductFacade $productFacade, ProductParameterRepository $productParameterRepository)
	{
		$this->productFacade = $productFacade;
		$this->productParameterRepository = $productParameterRepository;
	}
	/**
	 * @Route("/product/{slug}", name="product_detail")
	 * @Template("product/detail.html.twig")
	 */
	public function productDetailAction($slug)
	{
		$product = $this->productFacade->getBySlug($slug);
		if (!$product) {
			throw new NotFoundHttpException("Produkt neexistuje");
		}

		return [
			"product" => $product,
			"parameters" => $this->productParameterRepository->findByProduct($product),
		];
	}


}
