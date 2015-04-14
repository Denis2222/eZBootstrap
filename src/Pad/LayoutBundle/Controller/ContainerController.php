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

class ContainerController extends APIViewController
{
	public function generate(
	    $locationId,
	    $content
	)
	{
	    $searchService   = $this->container->get('search.controller');
		$configService   = $this->container->get('config.service');
		$contentTypeService = $this->getRepository()->getContentTypeService();
	    $locationService    = $this->getRepository()->getLocationService();

		$contentType = $contentTypeService->loadContentType( $content->contentInfo->contentTypeId );

        $config = $configService->GenerateConfig('container',$contentType->identifier);

	    $root 			  = $locationService->loadLocation( $locationId );
	    
	    $modificationDate = $root->contentInfo->modificationDate;

	    /* Query */
	    $postResults = $searchService->query(
	    	$locationId,
	    	$config['classes'],
	    	$config['limit']
	    );

        $components = array();
        foreach ( $postResults->searchHits as $hit )
        {
            $components[] = $hit->valueObject;
            //If any of the components is newer than the root, use that post's modification date
            if ($hit->valueObject->contentInfo->modificationDate > $modificationDate) {
                $modificationDate = $hit->valueObject->contentInfo->modificationDate;
            }
        }

        //Set the etag and modification date on the response
        $response = $this->buildResponse(
            __METHOD__ . $locationId,
            $modificationDate
        );

        $response->headers->set( 'X-Location-Id', $locationId );
        // Caching for 1min and make the cache vary on user hash
        $response->setSharedMaxAge( 60 );
        $response->setVary( 'X-User-Hash' );

	    //If nothing has been modified, return a 304
	    if ( $response->isNotModified($this->getRequest()) )
	    {
	        return $response;
	    }
	 
	 	$parameters = array(
	 		'components' => $components,
	 		'viewType' => $config['viewType']
	 	);

	    //Render the output
	    return $this->render(
	        $config['template'],
	        $parameters,
	        $response
	    );
	}
}
