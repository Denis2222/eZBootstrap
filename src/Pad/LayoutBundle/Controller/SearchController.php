<?php
namespace Pad\LayoutBundle\Controller;
//The above defines our PHP namespace

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Yaml\Yaml,
    eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController as APIViewController,
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;

class SearchController extends APIViewController
{
	public function query(
		$locationId,
		$typeIdentifiers=array(),
		$limit = 18,
		$sortMethods=array())
	{

		$searchService = $this->getRepository()->getSearchService();

		$query = new Query();

		$query->criterion = new Criterion\LogicalAnd(
			array(
				new Criterion\ParentLocationId( $locationId ),
				new Visibility(Visibility::VISIBLE),
				new Criterion\ContentTypeIdentifier( $typeIdentifiers )
			)
		);

		if($sortMethods == array()) {
			$sortMethods = array(new SortClause\LocationPriority(Query::SORT_ASC));
		}

		if ( !empty( $sortMethods ) )
		{
			$query->sortClauses = $sortMethods;
		}
		
		$query->limit = $limit;

		return $searchService->findContent( $query );
	}
}