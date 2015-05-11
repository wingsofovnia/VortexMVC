<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:02
 */

namespace vortex\auth;


interface UserProviderInterface {
    public function retrieveBy(array $options);
    public function retrieveByCredentials($identity, $password);
} 