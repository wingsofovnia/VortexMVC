<?php
/**
 * Project: OwnMVC
 * Author: Ilia Ovchinnikov
 * Date: 20-May-14
 * Time: 01:10
 */

namespace Application\Models;

use Vortex\Database\DAO\Entity;

class BeanModel extends Entity {
    private $privateProp = '_private';
    protected $protectedProp = '_protected';
    public $publicProp = '_public';
}