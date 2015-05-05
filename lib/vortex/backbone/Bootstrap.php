<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 05-May-15
 * Time: 13:22
 */

namespace vortex\backbone;


use vortex\http\Request;
use vortex\http\Response;

class Bootstrap {
    private $vertebrae = array();
    private $request;
    private $response;

    public function __construct(Request $request, Response $response) {
        if ($request == null)
            throw new \InvalidArgumentException('null $request');
        else if ($response == null)
            throw new \InvalidArgumentException('null $response');

        $this->request = $request;
        $this->response = $response;
    }


    public function addVertebra(VertebraInterface $vertebra) {
        if ($vertebra == null)
            throw new \InvalidArgumentException('null $vertebra');
        $this->vertebrae[] = $vertebra;
    }

    public function removeVertebra(VertebraInterface $vertebra) {
        if ($vertebra == null)
            throw new \InvalidArgumentException('null $vertebra');
        for ($i = 0; $i < count($this->vertebrae); $i++)
            if ($vertebra === $this->vertebrae[$i]) {
                unset($this->vertebrae[$i]);
                return;
            }
    }

    public function run() {
        /** @var $vertebra VertebraInterface */
        foreach ($this->vertebrae as $vertebra)
            $vertebra->process($this->request, $this->response);
    }
}