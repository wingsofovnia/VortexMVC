<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:01
 */

namespace vortex\auth;


interface UserInterface {
    public function getAuthIdentifier();
    public function getAuthPassword();
} 