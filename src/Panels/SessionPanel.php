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

class SessionPanel extends Panel {
    protected $session = array();

    public function __construct($session) {
        $this->session = $session;
    }

    public function getId() {
        return 'session';
    }

    public function getHtml() {
        ksort($this->session);

        $session = array(
            'ID'               => session_id(),
            'Name'             => session_name(),
            'Module Name'      => session_module_name(),
            'Cache Expire'     => session_cache_expire() . ' minutes',
            'Cache Limiter'    => session_cache_limiter(),
            'Cookie Lifetime'  => ini_get('session.cookie_lifetime'),
            'Cookie Path'      => ini_get('session.cookie_path'),
            'Cookie Domain'    => ini_get('session.cookie_domain'),
            'Cookie Secure'    => ini_get('session.cookie_secure'),
            'Cookie HTTP Only' => ini_get('session.cookie_httponly'),
        );

        $html .= "\t<table>\n";
        $html .= "\t<tbody>\n";
        foreach ($session as $key => $value) {
            $key = htmlspecialchars($key);
            $value = str_replace("  ", "&nbsp;&nbsp;", htmlspecialchars($value)); // @TODO var_dump
            $html .= "\t\t<tr><td>{$key}:</td><td>{$value}</td></tr>\n";
        }
        $html .= "\t</tbody>\n\t</table><br>\n";

        $html .= "\t<table>\n";
        $html .= "\t<thead><tr><td>key</td><td>value</td></tr></thead>\n";
        $html .= "\t<tbody>\n";
        foreach ($this->session as $key => $value) {
            $key = htmlspecialchars($key);
            $value = str_replace("  ", "&nbsp;&nbsp;", nl2br(htmlspecialchars(var_export($value, true)))); // @TODO var_dump
            $html .= "\t\t<tr><td>{$key}:</td><td>{$value}</td></tr>\n";
        }
        $html .= "\t</tbody>\n\t</table>\n";

        return $html;
    }

    public function getLabel() {
        return 'session';
    }
}

