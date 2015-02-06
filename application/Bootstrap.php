<?php
/**
 * Project: VortexMVC
 * Author: Illia Ovchynnikov
 * Date: 14-Sep-14
 */

namespace application;

use vortex\ABootstrap;
use vortex\utils\Logger;

class Bootstrap extends ABootstrap {
    public function initBootstrapTest() {
        Logger::debug("Bootstrap Initiator Works Fine!");
    }
} 