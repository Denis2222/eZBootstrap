<?php
namespace Pad\LayoutBundle\Service;
 
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
class ConfigService
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
 
    public function GenerateConfig($type = 'page', $class = 'full')
    {

    	$configGenerate = $this->configResolver->getParameter('generate', 'pad');
    	if (isset($configGenerate) && 
    		isset($configGenerate[$type]) && 
    		isset($configGenerate[$type][$class]) &&
            count($configGenerate[$type][$class])
    	)
    	{
            //die('ok');
    		return $configGenerate[$type][$class];
    	} elseif (isset($configGenerate['default']['default'])) {

    		return $configGenerate['default']['default'];
    	} else {
    		//die('not array');
    	}
    }
}