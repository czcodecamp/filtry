<?php

namespace AppBundle\Controller;

use AppBundle\Facade\CategoryFacade;
use AppBundle\Facade\ProductFacade;
use AppBundle\Service\Paginator;
use AppBundle\Service\FilterGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Service\Filter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 * @Route(service="app.controller.category_controller")
 */
class CategoryController
{
	private $categoryFacade;
	private $productFacade;
	private $filterService;
	private $filterGenerator;

	public function __construct(
		CategoryFacade $categoryFacade,
		ProductFacade $productFacade,
		Filter $filterService,
		ProductFacade $productFacade,
		FilterGenerator $filterGenerator
	) {

		$this->categoryFacade = $categoryFacade;
		$this->productFacade = $productFacade;
		$this->filterService = $filterService;
		$this->filterGenerator = $filterGenerator;
	}
	/**
	 * @Route("/vyber/{slug}/{page}", name="category_detail", requirements={"page": "\d+"}, defaults={"page": 1})
	 * @Template("category/detail.html.twig")
	 */
	public function categoryDetail($slug, $page, Request $request)
	{
		//filter
		$filterParams = $request->get('filter');	
		$data = $this->filterService->prepareParams($filterParams);

		$category = $this->categoryFacade->getBySlug($slug);

		if (!$category) {
			throw new NotFoundHttpException("Kategorie neexistuje");
		}

		$countByCategory = $this->productFacade->getCountByCategory($category, $data['query']);

		$paginator = new Paginator($countByCategory, 6);
		$paginator->setCurrentPage($page);
		
		$products = $this->productFacade->findByCategoryAndFilter($category, $data['query'], $paginator->getLimit(), $paginator->getOffset());
		return [
			"products" => $products,		    
			"categories" => $this->categoryFacade->getParentCategories($category),
			"category" => $category,
			"currentPage" => $page,
			"totalPages" => $paginator->getTotalPageCount(),
			"pageRange" => $paginator->getPageRange(5),
		    	"filter" => $filterParams,
		];
	}

}
