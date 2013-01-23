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

// @TODO implement an interface
// @TODO profiling flags: exec time, hit/miss, keys, values
class MemcacheProfiler extends Profiler {
    private $keys = array();
    private $servers = array();

    public function __construct($memcache, $enable_detailed_logging=false) {
        parent::__construct($memcache, $enable_detailed_logging);
        $this->stats = array(
            // 'bytes_read'    => 0,
            // 'bytes_written' => 0,
            'cmd_get'    => 0,
            'cmd_set'    => 0,
            'get_hits'   => 0,
            'get_misses' => 0,
            'get_time'   => 0.0,
            'get_unique' => 0.0,
            'set_time'   => 0.0,
            'total_cmd'  => 0,
            'total_time' => 0.0,
        );
    }

    public function addServer($host, $port=11211, $persistent=true, $weight=0, $timeout=null, $retry_interval=null, $status=true, $failure_callback=null, $timeoutms=null) {
        $this->servers[$host.':'.$port] = true;
        return $this->component->addServer($host, $port, $persistent, $weight, $timeout, $retry_interval, $status, $failure_callback, $timeoutms);
    }

    public function connect($host, $port=null, $timeout=null) {
        $t0 = microtime(true);
        $result = $this->component->connect($host, $port, $timeout);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
        $this->stats['cmd_connect']  += 1;
        $this->stats['connect_time'] += $elapsed;
        $this->stats['total_cmd']    += 1;
        $this->stats['total_time']   += $elapsed;

        $this->log($t0, $t1, 'connect', func_get_args(), $result);
        return $result;
    }

    public function get($keys, $flags=null) {
        $t0 = microtime(true);
        $result = $this->component->get($keys, $flags);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
        $this->stats['get_time']   += $elapsed;
        $this->stats['total_time'] += $elapsed;
        if (is_array($keys)) {
            $cmd_get  = count($keys);
            $get_hits = count($result);
            // $this->stats['bytes_read'] += array_reduce_strlen($result);
            $this->stats['cmd_get']    += $cmd_get;
            $this->stats['get_hits']   += $get_hits;
            $this->stats['get_misses'] += $cmd_get - $get_hits;
            $this->stats['total_cmd']  += $cmd_get;
            foreach ($keys as $key) {
                $this->keys[$key] = true;
            }
            $full_result = $result + array_fill_keys(array_diff($keys, array_keys($result)), false);
        } else {
            // $this->stats['bytes_read'] += ($result !== false ? strlen($result) : 0);
            $this->stats['cmd_get']    += 1;
            $this->stats['get_hits']   += ($result !== false ? 1 : 0);
            $this->stats['get_misses'] += ($result === false ? 1 : 0);
            $this->stats['total_cmd']  += 1;
            $this->keys[$key] = true;
            $full_result = $result;
        }

        $this->log($t0, $t1, 'get', func_get_args(), $full_result);
        return $result;
    }

    public function set($key, $value, $flag=0, $expiration=0) {
        $t0 = microtime(true);
        $result = $this->component->set($key, $value, $flag, $expiration);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
        // $this->stats['bytes_written'] += strlen($value);
        $this->stats['cmd_set']    += 1;
        $this->stats['set_time']   += $elapsed;
        $this->stats['total_cmd']  += 1;
        $this->stats['total_time'] += $elapsed;

        $this->log($t0, $t1, 'set', func_get_args(), $result);
        return $result;
    }

    public function getStats() {
        $this->stats['get_unique'] = count($this->keys);
        return parent::getStats();
    }

    public function createPanel() {
        return new DatabasePanel($this);
    }
}

