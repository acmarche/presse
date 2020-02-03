<?php
/**
 * This file is part of presse application
 * @author jfsenechal <jfsenechal@gmail.com>
 * @date 14/10/19
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace AcMarche\Presse\Service;


class PresseService
{
    public static function getRoles()
    {
        return ['ROLE_PRESSE', 'ROLE_PRESSE_ADMIN'];
    }
}