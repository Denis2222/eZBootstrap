<?php
namespace Pad\LayoutBundle\Controller;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    eZ\Bundle\EzPublishCoreBundle\Controller;

class MenuController extends Controller
{
    public function indexAction()
    {
        //Render the output
        return $this->render(
            'PadLayoutBundle:global:menu.html.twig',
            array(
                'menu' => $this->container->get("pad.menu.top")
            ),
            new Response()
        );
    }
}
