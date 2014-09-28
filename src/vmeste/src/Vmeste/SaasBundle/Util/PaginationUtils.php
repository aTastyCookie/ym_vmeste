<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/21/14
 * Time: 10:17 PM
 */

namespace Vmeste\SaasBundle\Util;


class PaginationUtils {

    /**
     * @param $page
     * @param $pageOnSidesLimit
     * @param $pageCount
     * @return array
     */
    public static function generatePaginationPageNumbers($page, $pageOnSidesLimit, $pageCount)
    {

        $pageNumberArray = array();

        if ($page > $pageOnSidesLimit + 1) {
            for ($i = $page - $pageOnSidesLimit; $i < $page; $i++) {
                array_push($pageNumberArray, $i);
            }
        } else {
            for ($i = 1; $i < $page; $i++) {
                array_push($pageNumberArray, $i);
            }
        }

        array_push($pageNumberArray, $page);

        if ($page + $pageOnSidesLimit < $pageCount) {
            for ($i = $page + 1; $i <= $page + $pageOnSidesLimit; $i++) {
                array_push($pageNumberArray, $i);
            }
            return array($pageNumberArray, $page);
        } else {
            for ($i = $page + 1; $i <= $pageCount; $i++) {
                array_push($pageNumberArray, $i);
            }
            return $pageNumberArray;
        }
    }

} 