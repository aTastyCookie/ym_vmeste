<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 10/5/14
 * Time: 8:25 PM
 */

namespace Vmeste\SaasBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ForbiddenUriValidator extends ConstraintValidator
{

    private $em;

    private $systemRoutes = array(
        'campaign',
        'transaction',
        'admin',
        'logout',
        'login',
        'index',
        'customer',
        'bundles',
        'uploads',
        'app',
        'yandex',
        'kassa',
        'soberem',
        'na'
    );

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($value, Constraint $constraint)
    {

        $defaultOptions = $constraint->getDefaultOption();

        if (is_array($defaultOptions) && array_key_exists('previousUri', $defaultOptions) && !is_null($defaultOptions['previousUri'])) {
            if (strcmp($value, $defaultOptions['previousUri']) === 0) return;
        }

        $campaign = $this->em->getRepository('Vmeste\SaasBundle\Entity\Campaign')->findOneBy(array('url' => $value));

        if (in_array($value, $this->systemRoutes) || !is_null($campaign)) {
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
        }
    }


}