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

class PearDbProfiler extends Profiler {
    private $queries = array();
    public function connect($dsn, $persistent = false) {
        $t0 = microtime(true);
        $result = $this->component->connect($dsn, $persistent);
        $t1 = microtime(true);

        $elapsed = $t1 - $t0;
        $this->stats['cmd_connect']  += 1;
        $this->stats['connect_time'] += $elapsed;
        $this->stats['total_cmd']    += 1;
        $this->stats['total_time']   += $elapsed;

        $this->log($t0, $t1, 'connect', func_get_args(), $result);
        return $result;
    }

    public function execute($stmt, $params = array()) {
        $t0 = microtime(true);
        $result = $this->component->execute($stmt, $params);
        $t1 = microtime(true);

        $this->updateQueryStats($t0, $t1);

        // @TODO wrap the resultset?
        return $result;
    }

    public function query($query, $params = array()) {
        $t0 = microtime(true);
        $result = $this->component->query($query, $params);
        $t1 = microtime(true);

        $this->updateQueryStats($t0, $t1);

        // @TODO wrap the resultset?
        return $result;
    }

    private function updateQueryStats($start_time, $end_time) {
        $elapsed = $end_time - $start_time;
        $query = $this->component->last_query;

        $hash = md5($query);
        if (!isset($this->queries[$hash])) {
            $this->queries[$hash] = $this->getSqlCommand($query);
        }
        $cmd = $this->queries[$hash];

        $this->stats["cmd_{$cmd}"]  += 1;
        // $this->stats['rows']       += $result->numRows();
        $this->stats["{$cmd}_time"] += $elapsed;
        $this->stats['total_cmd']   += 1;
        $this->stats['total_time']  += $elapsed;

        $this->log($start_time, $end_time, $cmd, $query, $result);
    }

    public function getStats() {
        $this->stats['total_unique'] = count($this->queries);
        return parent::getStats();
    }

    private function getSqlCommand($query) {
        $pos = 0;
        $cmd = false;
        while (isset($query[$pos])) {
            $char = $query[$pos];
            $ord = ord($char);
            if ((65 <= $ord && $ord <= 90) || (97 <= $ord && $ord <= 122)) {
                $cmd .= $char;
            } elseif (isset($cmd[0])) {
                break;
            }
            $pos += 1;
        }
        return strtolower($cmd);
    }

    public function createPanel() {
        return new DatabasePanel($this);
    }
}

