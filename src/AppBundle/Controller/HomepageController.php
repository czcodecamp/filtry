<?php
namespace AppBundle\Controller;
use AppBundle\Facade\CategoryFacade;
use AppBundle\Facade\ProductFacade;
use AppBundle\Facade\UserFacade;
use AppBundle\Service\Paginator;
use AppBundle\Service\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vašek Boch <vasek.boch@live.com>
 * @author Jan Klat <jenik@klatys.cz>
 * @author Aleš Kůdela <kudela.ales@gmail.com>
 * @Route(service="app.controller.homepage_controller")
 */
class HomepageController
{

	private $productFacade;
	private $categoryFacade;
	private $userFacade;
	private $filterService;	

	public function __construct(
		ProductFacade $productFacade,
		CategoryFacade $categoryFacade,
		UserFacade $userFacade,
		Filter $filterService
	) {

		$this->productFacade = $productFacade;
		$this->categoryFacade = $categoryFacade;
		$this->userFacade = $userFacade;
		$this->filterService = $filterService;
	}

	/**
	 * @Route("/", name="homepage")
	 * @Template("homepage/homepage.html.twig")
	 */
	public function homepageAction(Request $request)
	{
		//filter
		$filterParams = $request->get('filter');
		$data = $this->filterService->prepareParams($filterParams);

		$page = intval($request->get('page', 1));
		$count = $this->productFacade->countAllProducts($data['query']);
		$paginator = new Paginator($count, 6);
		$paginator->setCurrentPage($page);

		return [
			"products" => $this->productFacade->getAllByFilter($paginator->getLimit(), $paginator->getOffset(), $data['query']),
			"categories" => $this->categoryFacade->getTopLevelCategories(),
			"currentPage" => $page,
			"totalPages" => $paginator->getTotalPageCount(),
			"pageRange" => $paginator->getPageRange(5),
			"user" => $this->userFacade->getUser(),
			"filter" => $filterParams,
		];
	}

}
