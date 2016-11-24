<?php

namespace AppBundle\Controller;

use AppBundle\Facade\CategoryFacade;
use AppBundle\Facade\ProductFacade;
use AppBundle\Service\Paginator;
use AppBundle\Service\FilterGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 * @Route(service="app.controller.category_controller")
 */
class CategoryController
{
	private $categoryFacade;
	private $productFacade;
	private $filterGenerator;

	public function __construct(
		CategoryFacade $categoryFacade,
		ProductFacade $productFacade,
		FilterGenerator $filterGenerator
	) {

		$this->categoryFacade = $categoryFacade;
		$this->productFacade = $productFacade;
		$this->filterGenerator = $filterGenerator;
	}
	/**
	 * @Route("/vyber/{slug}/{page}", name="category_detail", requirements={"page": "\d+"}, defaults={"page": 1})
	 * @Template("category/detail.html.twig")
	 */
	public function categoryDetail($slug, $page)
	{
		$category = $this->categoryFacade->getBySlug($slug);

		if (!$category) {
			throw new NotFoundHttpException("Kategorie neexistuje");
		}

		$countByCategory = $this->productFacade->getCountByCategory($category);

		$paginator = new Paginator($countByCategory, 6);
		$paginator->setCurrentPage($page);
		return [
			"products" => $this->productFacade->findByCategory($category, $paginator->getLimit(), $paginator->getOffset()),
			"categories" => $this->categoryFacade->getParentCategories($category),
			"category" => $category,
			"currentPage" => $page,
			"totalPages" => $paginator->getTotalPageCount(),
			"pageRange" => $paginator->getPageRange(5),
			"testFilters" => $this->getTestFilters($category->getId()),
		];
	}

	private function getTestFilters($categoryId)
	{
		$testInputFilters = [
			1 => ['Apple', 'Samsung'],
			2 => [10000, 30000],
			3 => true,
		];

		$this->filterGenerator->setInputFilters($categoryId, $testInputFilters);

		return $this->filterGenerator->generateFilters();
	}
}
