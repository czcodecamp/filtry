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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

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
	private $router;

	public function __construct(
		CategoryFacade $categoryFacade,
		ProductFacade $productFacade,
		Filter $filterService,
		FilterGenerator $filterGenerator,
		RouterInterface $router
	) {
		$this->categoryFacade = $categoryFacade;
		$this->productFacade = $productFacade;
		$this->filterService = $filterService;
		$this->filterGenerator = $filterGenerator;
		$this->router = $router;
	}
	/**
	 * @Route("/vyber/{slug}/{page}", name="category_detail", requirements={"page": "\d+"}, defaults={"page": 1})
	 * @Template("category/detail.html.twig")
	 */
	public function categoryDetail($slug, $page, Request $request)
	{
		$filtering = $request->get('filtering');
		if ($filtering) {
			$filterParams = $this->filterService->createLinkParam($filtering);	
			$link = urldecode($this->router->generate('category_detail', array('filter' => $filterParams, 'slug' => $slug, 'page' => $page)));
			return RedirectResponse::create($link);
		}

		//filter
		if(!isset($filterParams)) {
			$filterParams = $request->get('filter');
		}	
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
			"usedFilters" => $data['filter'],
			"filterOptions" => $this->getFilterOptions($category->getId(), $data['filter']),
		];
	}

	private function getFilterOptions($categoryId, $inputFilters)
	{
		$this->filterGenerator->setInputFilters($categoryId, $inputFilters);
		return $this->filterGenerator->generateFilters();
	}
}
