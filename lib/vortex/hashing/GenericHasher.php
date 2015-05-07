<?php
/**
 * Project: vortex.com
 * Author: superuser
 * Date: 07-May-15
 * Time: 18:15
 */

namespace vortex\hashing;


class GenericHasher implements HasherInterface {

    public function make($value, array $options = array()) {
        return md5($value . $this->salt($value));
    }

    public function check($value, $hash, array $options = array()) {
        return $hash == $this->make($value);
    }

    private function salt($value) {
        return sha1($value);
    }
}