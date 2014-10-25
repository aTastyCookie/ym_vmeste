<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/14/14
 * Time: 5:03 PM
 */

namespace Vmeste\SaasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CustomerController extends Controller {

    /**
     * @Template()
     */
    public function homeAction() {

//        $usr= $this->get('security.context')->getToken()->getUser();
//        $usr->getUsername();



        return array();
    }

} 