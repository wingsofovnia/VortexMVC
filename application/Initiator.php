<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 14-Sep-14
 */

namespace application;

use vortex\application\Bootstrap;
use vortex\utils\Logger;

class Initiator extends Bootstrap {
    public function initBoostrapTest() {
        Logger::debug("Bootstrap Initiator Works Fine!");
    }
} 