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

class ServerInfoPanel extends Panel {
    public function getId() {
        return 'server';
    }

    public function getHtml() {
        $properties = array(
            'Host Name'         => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')),
            'Host Address'      => (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''),
            'System Load'       => implode(' ', sys_getloadavg()),
            'Peak Memory Usage' => number_format(memory_get_peak_usage()/1048576, 1) . ' MB / ' . (ini_get('memory_limit') == -1 ? 'unlimited' : ini_get('memory_limit') . ' MB'),
            'PHP Version'       => phpversion(). ' (' . PHP_SAPI . ')',
            'PHP Extensions'    => count(get_loaded_extensions()),
            'OB Handlers'       => implode(', ', ob_list_handlers()),
        );
        $html = "\t<h4>" . php_uname() . "</h4>\n";
        foreach ($properties as $k => $v) {
            $html .= "\t<strong>{$k}</strong>: {$v}<br>\n";
        }
        return $html;
    }

    public function getLabel() {
        return 'server';
    }
}

