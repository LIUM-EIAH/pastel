<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

interface HashCodeProvider {
    public function getHashCode();
}

class HashMap implements ArrayAccess {

    private $keys = array();

    private $values = array();

    public function __construct($values = array()) {
        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function offsetExists($offset) {
        $hash = $this->getHashCode($offset);

        return isset($this->values[$hash]);
    }

    public function offsetGet($offset) {
        $hash = $this->getHashCode($offset);

        return $this->values[$hash];
    }

    public function offsetSet($offset, $value) {
        $hash = $this->getHashCode($offset);

        $this->keys[$hash] = $offset;
        $this->values[$hash] = $value;
    }

    public function offsetUnset($offset) {
        $hash = $this->getHashCode($offset);

        unset($this->keys[$hash]);
        unset($this->values[$hash]);
    }

    public function keys() {
        return array_values($this->keys);
    }

    public function values() {
        return array_values($this->values);
    }

    private function getHashCode($object) {
        if (is_object($object)) {
            if ($object instanceof HashCodeProvider) {
                return $object->getHashCode();
            } else {
                return spl_object_hash($object);
            }
        } else {
            return $object;
        }
    }

}
