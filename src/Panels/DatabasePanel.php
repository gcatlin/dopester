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

class DatabasePanel extends ProfilerPanel {
    public function getHtml() {
        $profiler = $this->profiler;
        $log = $profiler->getLog();
        $stats = $profiler->getStats();
        $show_commands = array_flip(array('select', 'insert', 'update', 'delete'));

        $html = sprintf(
            "%d Queries  %d SELECT  %d INSERT  %d UPDATE  %d DELETE<br>",
            $stats['cmd_select'] + $stats['cmd_insert'] + $stats['cmd_update'] + $stats['cmd_delete'],
            $stats['cmd_select'],
            $stats['cmd_insert'],
            $stats['cmd_update'],
            $stats['cmd_delete']
        );
        $html .= "\t<table>\n";
        $html .= "\t<thead><tr><th>#</th><th>command</th><th>query</th><th>time</th></tr>\n\t</thead>\n";
        $html .= "\t<tbody>\n";
        $i = 0;
        foreach ($log as $event) {
            if (isset($show_commands[$event[0]])) {
                $i += 1;
                $time = number_format(($event[3][1] - $event[3][0]) * 1000, 3);
                $query = htmlspecialchars($event[1]);
                $html .= "<tr><td>{$i}</td><td>{$event[0]}</td><td>$query</td><td>{$time}ms</td></tr>\n";
            }
        }
        $html .= "</tbody></table>\n";
        ob_start();
        var_dump($this->commands);
        var_dump($stats);
        var_dump($log);
        return $html . ob_get_clean();
    }

    public function getLabel() {
        $stats = $this->profiler->getStats();
        $num_queries = $stats['cmd_select'] + $stats['cmd_insert'] + $stats['cmd_update'] + $stats['cmd_delete'];
        return number_format($this->elapsed_ms) . 'ms / ' . $num_queries . ' (' . $stats['total_unique'] . ') sql';
    }
}

