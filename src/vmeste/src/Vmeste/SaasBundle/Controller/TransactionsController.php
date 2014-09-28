<?php
namespace Vmeste\SaasBundle\Controller;


use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class TransactionsController extends Controller
{

    /**
     * @Template
     */
    public function homeAction()
    {
    	return array();
    }
}
?>