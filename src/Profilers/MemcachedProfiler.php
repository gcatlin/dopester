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

class MemcachedProfiler extends Profiler {
    private $keys = array();
    private $servers = array();

    public function __construct($memcache, $enable_detailed_logging=false) {
        parent::__construct($memcache, $enable_detailed_logging);
        $this->stats = array(
            'cmd_get'    => 0,
            'cmd_set'    => 0,
            'get_hits'   => 0,
            'get_misses' => 0,
            'get_time'   => 0.0,
            'set_time'   => 0.0,
            'total_cmd'  => 0,
            'total_time' => 0.0,
        );
    }

    public function addServer($host, $port, $weight=0) {
        $this->servers[$host.':'.$port] = true;
        return $this->component->addServer($host, $port, $weight);
    }

    public function addServers($servers) {
        foreach ($servers as $server) {
            list($host, $port, $weight) = $server;
            $this->servers[$host.':'.$port] = true;
        }
        return $this->component->addServers($servers);
    }

    public function get($key, $cache_callback=null, &$cas_token=null) {
        $t0 = microtime(true);
        $result = $this->component->get($key, $cache_callback, $cas_token);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
        $miss = ($result === false && $this->getResultCode() === Memcached::RES_NOTSTORED);
        $this->stats['cmd_get']    += 1;
        $this->stats['get_hits']   += ($miss !== false ? 1 : 0);
        $this->stats['get_misses'] += ($miss === false ? 1 : 0);
        $this->stats['get_time']   += $elapsed;
        $this->stats['total_cmd']  += 1;
        $this->stats['total_time'] += $elapsed;
        $this->keys[$key] = true;

        $this->log($t0, $t1, 'get', func_get_args(), $result); // @TODO include result code?
        return $result;
    }

    public function replace($key, $value, $expiration=0) {
        $t0 = microtime(true);
        $result = $this->component->replace($key, $value, $expiration);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
        $this->stats['cmd_set']    += 1;
        $this->stats['set_time']   += $elapsed;
        $this->stats['total_cmd']  += 1;
        $this->stats['total_time'] += $elapsed;

        $this->log($t0, $t1, 'replace', func_get_args(), $result);
        return $result;
    }

    public function set($key, $value, $expiration=0) {
        $t0 = microtime(true);
        $result = $this->component->set($key, $value, $expiration);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
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
        return new CachePanel($this);
    }
}

