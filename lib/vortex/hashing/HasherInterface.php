<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:12
 */

namespace vortex\hashing;


interface HasherInterface {
    public function make($value, array $options = array());
    public function check($value, $hash, array $options = array());
}