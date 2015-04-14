<?php
namespace Pad\LayoutBundle\EventListener;
 
use eZ\Publish\Core\MVC\ConfigResolverInterface,
    eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    eZ\Publish\API\Repository\Repository;
 
/**
 * PreContentViewListener hooks the PreContentView Event to provide extra data to the template
 */
class PreContentViewListener
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;
 
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;
 
 
    /**
     * Constructs our listener and loads it with access to the eZ Publish repository and config
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct( Repository $repository, ConfigResolverInterface $configResolver )
    {
        //Add these to the class so we have them when the event method is triggered
        $this->repository = $repository;
        $this->configResolver = $configResolver;
    }
 
    /**
     * Fires just before the page is rendered
     * @param \eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent $event
     */
    public function onPreContentView( PreContentViewEvent $event )
    {
        //What's our design/surround object in the repository called? Check the config
        //This reads the setting we added to parameters.yml
        //$componentTypes = $this->configResolver->getParameter('page', 'pad');
 		/*
        //To retrieve the surround object, first access the repository
        $searchService = $this->repository->getSearchService();
 
        //Find the first object that matched the name from our config
        //(We only expect there to be one in the DB)
        $surround = $searchService->findSingle(
            new Criterion\ContentTypeIdentifier($surroundTypeIdentifier)
        );
        */
 
        //Retrieve the view context from the event
        //$contentView = $event->getContentView();
 
        //Add the surround variable to the context
        //$contentView->addParameters( array('component' => $componentTypes) );
    }
}