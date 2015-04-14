<?php
/**
 * This file is part of the DemoBundle package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace Pad\MenuBundle\Menu;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use eZ\Publish\Core\Helper\TranslationHelper;

/**
 * A simple eZ Publish menu provider.
 *
 * Generates a multi level menu, starting from the configured root node.
 */
class Builder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    public function __construct(
        FactoryInterface $factory,
        SearchService $searchService,
        RouterInterface $router,
        ConfigResolverInterface $configResolver,
        LocationService $locationService,
        TranslationHelper $translationHelper
    )
    {
        $this->factory = $factory;
        $this->searchService = $searchService;
        $this->router = $router;
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
        $this->translationHelper = $translationHelper;
    }

    public function createTopMenu( Request $request )
    {
        
        $menu = $this->factory->createItem( 'root' );
        $this->addLocationsToMenu(
            $menu,
            $this->getMenuItems(
                $this->configResolver->getParameter( 'content.tree_root.location_id' )
            )
        );
        
        return $menu;
    }

    /**
     * Adds locations from $searchHit to $menu
     *
     * @param ItemInterface $menu
     * @param SearchHit[] $searchHits
     * @return void
     */
    private function addLocationsToMenu( ItemInterface $menu, array $searchHits )
    {
        $parents = array();
        foreach ( $searchHits as $key => $searchHit )
        {
            $location = $searchHit->valueObject;
            if (isset($parents[$location->parentLocationId])) {
                $parents[$location->parentLocationId]++;
            } else {
                $parents[$location->parentLocationId] = 1;
            }
        }
        foreach ( $searchHits as $key => $searchHit )
        {
            $location         = $searchHit->valueObject;
            $parentPath = $this->parentPath($location);
            $menuItem = $menu;
            if(count($parentPath)) {
                for($x=0; $x<count($parentPath); $x++) {
                    $menuItem = $menuItem[$parentPath[$x]];
                }
            }

            if (isset($parents[$location->id])) {
                $attributes = array('class' => 'menu-item-parent');
            } else {
                $attributes = array();
            }

            $menuItem->addChild(
                $location->id,
                array(
                    'label' => $this->translationHelper->getTranslatedContentNameByContentInfo( $location->contentInfo ),
                    'uri' => $this->router->generate( $location ),
                    'attributes' => $attributes
                )
            );
            if(count($parentPath)) {
                $menuItem->setChildrenAttribute( 'class', 'sub-menu' );
            }
        }
    }

    private function parentPath($location)
    {
        /*
        /1/2/77/133/145/178/  => 133/145
        /1/2/77/133/145/  => 133
        */
        $arr = explode('/', $location->pathString);
        $arr = array_slice($arr, 4, -2);
        if(empty($arr)) {
            return array();
        }
        return explode('/',implode('/', $arr));
    }

    /**
     * Queries the repository for menu items, as locations filtered on the list in TopIdentifierList in menu.ini
     * @param int|string $rootLocationId Root location for menu items. Only two levels below this one are searched
     * @return SearchHit[]
     */
    private function getMenuItems( $rootLocationId )
    {
        $rootLocation = $this->locationService->loadLocation( $rootLocationId );

        $query = new LocationQuery();

        $query->query = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentTypeIdentifier( $this->getTopMenuContentTypeIdentifierList() ),
                new Criterion\Visibility( Criterion\Visibility::VISIBLE ),
                new Criterion\Location\Depth(
                    Criterion\Operator::BETWEEN,
                    array( $rootLocation->depth , $rootLocation->depth + 10 )
                ),
                new Criterion\Subtree( $rootLocation->pathString ),
                new Criterion\LanguageCode( $this->configResolver->getParameter( 'languages' ) )
            )
        );
        $query->sortClauses = array( new Query\SortClause\Location\Path() );

        return $this->searchService->findLocations( $query )->searchHits;
    }

    private function getTopMenuContentTypeIdentifierList()
    {
        return array('page','landing_page','page_blog');
    }
}
