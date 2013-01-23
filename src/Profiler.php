<?php
// Copyright 2013 Geoff Catlin
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace gcatlin\dopester;

abstract class Profiler { // implements Something { // ???
    private static $profilers = array(); // @TODO use SplObjectStorage

    protected $component;
    protected $log = array();
    protected $stats = array('total_cmd' => 0, 'total_time' => 0.0);
    protected $enable_detailed_logging;

    protected $method_exists = array();
    protected $property_exists = array();

    public static function getRegisteredProfilers() {
        return self::$profilers;
    }

    private static function registerProfiler($profiler) {
        self::$profilers[] = $profiler;
    }

    public function __construct($component, $enable_detailed_logging=false) {
        self::registerProfiler($this);
        $this->component = $component;
        $this->enable_detailed_logging = (bool) $enable_detailed_logging;
    }

    public function __call($name, $args) {
        if (!isset($this->method_exists[$name])) {
            $this->method_exists[$name] = method_exists($this->component, $name);
        }

        if ($this->method_exists[$name]) {
            $t0 = microtime(true);
            $result = call_user_func_array(array($this->component, $name), $args);
            $t1 = microtime(true);
            $this->log($t0, $t1, $name, $args, $result);
            $this->stats['total_time'] += $t1 - $t0;
            return $result;
        }
    }

    public function __get($name) {
        if (!isset($this->property_exists[$name])) {
            $this->property_exists[$name] = property_exists($this->component, $name);
        }

        if ($this->property_exists[$name]) {
            return $this->component->$name;
        }
    }

    public function __set($name, $value) {
        if (!isset($this->property_exists[$name])) {
            $this->property_exists[$name] = property_exists($this->component, $name);
        }

        if ($this->property_exists[$name]) {
            $this->component->$name = $value;
        }
    }

    public function __isset($name) {
        if (!isset($this->property_exists[$name])) {
            $this->property_exists[$name] = property_exists($this->component, $name);
        }

        if ($this->property_exists[$name]) {
            return isset($this->component->$name);
        }
    }

    public function __unset($name) {
        if (!isset($this->property_exists[$name])) {
            $this->property_exists[$name] = property_exists($this->component, $name);
        }

        if ($this->property_exists[$name]) {
            unset($this->component->$name);
        }
    }

    public function getLog() {
        return $this->log;
    }

    public function getStats() {
        ksort($this->stats);
        return $this->stats;
    }

    public function log($start_time, $end_time, $name, $input = array(), $output = array()) {
        if (!$this->enable_detailed_logging) {
            $input = array();
            $output = array();
        }
        $this->log[] = array($name, $input, $output, array($start_time, $end_time));
    }

    abstract public function createPanel();
}

