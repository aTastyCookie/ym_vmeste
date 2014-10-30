<?php

namespace Vmeste\SaasBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IndexController extends Controller {

    /**
     * @Template()
     */
    public function homeAction() {
        $apphost = $this->container->getParameter('recurrent.apphost');
        return array(
            'apphost' => $apphost
        );
    }
} 