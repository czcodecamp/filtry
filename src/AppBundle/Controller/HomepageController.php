<?php
namespace AppBundle\Controller;
use AppBundle\Facade\CategoryFacade;
use AppBundle\Facade\ProductFacade;
use AppBundle\Facade\UserFacade;
use AppBundle\Service\Paginator;
use AppBundle\Service\Filter;
use AppBundle\Service\FilterGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;


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
	private $router;

	public function __construct(
		ProductFacade $productFacade,
		CategoryFacade $categoryFacade,
		UserFacade $userFacade,
		Filter $filterService,
		FilterGenerator $filterGenerator,
		RouterInterface $router
	) {

		$this->productFacade = $productFacade;
		$this->categoryFacade = $categoryFacade;
		$this->userFacade = $userFacade;
		$this->filterService = $filterService;
		$this->filterGenerator = $filterGenerator;
		$this->router = $router;
	}

	/**
	 * @Route("/", name="homepage")
	 * @Template("homepage/homepage.html.twig")
	 */
	public function homepageAction(Request $request)
	{
		$filtering = $request->get('filtering');
		if ($filtering) {
			$filterParams = $this->filterService->createLinkParam($filtering);	
			$link = urldecode($this->router->generate('homepage', array('filter' => $filterParams)));
			return RedirectResponse::create($link);
		}		
		//filter
		if(!isset($filterParams)) {
			$filterParams = $request->get('filter');
		}
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
			"usedFilters" => $data['filter'],
			"filterOptions" => $this->getFilterOptions($data['filter']),
		];
	}

	private function getFilterOptions($inputFilters)
	{
		$this->filterGenerator->setInputFilters(null, $inputFilters);
		return $this->filterGenerator->generateFilters();
	}
}
