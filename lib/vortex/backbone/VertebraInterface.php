<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 05-May-15
 * Time: 13:24
 */

namespace vortex\backbone;


use vortex\http\Request;
use vortex\http\Response;

interface VertebraInterface {
    public function process(Request $request, Response $response);
}