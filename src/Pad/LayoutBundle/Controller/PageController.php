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

class PageController extends APIViewController
{
    public function generate($locationId, $content)
    {
        $locationService = $this->getRepository()->getLocationService();
        $searchService   = $this->container->get('search.controller');
        $configService   = $this->container->get('config.service');

        $location   = $locationService->loadLocation( $locationId );
        $modificationDate = $location->contentInfo->modificationDate;

        $config = $configService->GenerateConfig('page','full');
        $postResults = $searchService->query(
            $locationId,
            $config['classes'],
            $config['limit']
        );

        $preContent = array();
        $postContent = array();
        foreach ( $postResults->searchHits as $hit )
        {
            $locations = $locationService->loadLocation( $hit->valueObject->versionInfo->contentInfo->mainLocationId );
            if($locations->priority > 0) {
                $postContent[]  = $hit->valueObject;
            } else {
                $preContent[] = $hit->valueObject;
            }

            //If any of the posts is newer than the root, use that post's modification date
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
        // Caching for 1h and make the cache vary on user hash
        $response->setSharedMaxAge( 10 );
        $response->setVary( 'X-User-Hash' );
        //If nothing has been modified, return a 304
        if ( $response->isNotModified( $this->getRequest() ) )
        {
            return $response;
        }

        $parameter = array(
            'preContent'  => $preContent, 
            'postContent' => $postContent, 
            'viewType' => $config['viewType'],
            'content' => $content
        );

        //Render the output
        return $this->render(
            $config['template'],
            $parameter,
            $response
        );
    }
}
