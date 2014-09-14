<?php
/**
 * Project: VortexMVC
 * Author: Ilia Ovchinnikov
 * Date: 14-Sep-14
 */

namespace Application;

use Vortex\Bootstrap;
use Vortex\GlobalRegistry;
use Vortex\Logger;

class Initiator extends Bootstrap {
    public function initBoostrapTest() {
        Logger::debug("Bootstrap Initiator Works Fine!");
    }
} 