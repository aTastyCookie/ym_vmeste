<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/23/14
 * Time: 9:44 PM
 */

namespace Vmeste\SaasBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vmeste\SaasBundle\Entity\Settings;

class SettingsController extends Controller
{

    /**
     * @Template
     */
    public function editSettingsAction()
    {

        $user = $this->get('security.context')->getToken()->getUser();



        return array();
    }

    public function updateEmailSettingsAction()
    {

    }

    public function updateYkSettings()
    {

    }

    public function updatePassword()
    {

    }

} 