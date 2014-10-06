<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 10/5/14
 * Time: 8:44 PM
 */

namespace Vmeste\SaasBundle\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotaion
 */
class ForbiddenUriConstraint extends Constraint
{

    public $message = '"%string%" уже используется!';

    public function validatedBy()
    {
        return 'forbidden_uri';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

} 