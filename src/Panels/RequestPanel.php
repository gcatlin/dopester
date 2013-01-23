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

class RequestPanel extends Panel {
    protected $server;
    protected $post;
    protected $files;

    public function __construct($server, $post, $files) {
        $this->server = (array) $server;
        $this->post   = (array) $post;
        $this->files  = (array) $files;
    }

    public function getId() {
        return 'request';
    }

    public function getHtml() {
        $req = $this->server;

        $headers = array();
        foreach ($req as $name => $value) {
            if (strpos($name, 'HTTP_') === 0 || strpos($name, 'X_') === 0) {
                if ($name[0] == 'H') {
                    $name = substr($name, 5);
                }
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
                $headers[$name] = $value;
            }
        }
        if (empty($headers['Host'])) {
            $headers['Host'] = (isset($req['SERVER_NAME']) ? $req['SERVER_NAME'] : 'localhost');
        }
        if (isset($req['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $req['CONTENT_LENGTH'];
        }
        if (isset($req['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $req['CONTENT_TYPE'];
        }
        ksort($headers);

        $remote_addr = (isset($req['REMOTE_ADDR']) ? $req['REMOTE_ADDR'] : '');
        $remote_host = (isset($req['REMOTE_HOST']) ? $req['REMOTE_HOST'] : '');
        $remote_port = (isset($req['REMOTE_PORT']) ? ':' . $req['REMOTE_PORT'] : '');

        $method = (isset($req['REQUEST_METHOD']) ? $req['REQUEST_METHOD'] : 'GET');
        $scheme = (!empty($req['HTTPS']) && $req['HTTPS'] !== 'off' ? 'https' : 'http');
        $host = $headers['Host'];
        $port = (isset($req['SERVER_PORT']) ? ':' . $req['SERVER_PORT'] : '');
        $path = (isset($req['SCRIPT_NAME']) ? $req['SCRIPT_NAME'] : '') . (isset($req['PATH_INFO']) ? $req['PATH_INFO'] : '');
        $path = ($path ? $path : '/');
        $path .= (!empty($req['QUERY_STRING']) ? '?' . $req['QUERY_STRING'] : '');
        $ver = (isset($req['SERVER_PROTOCOL']) ? $req['SERVER_PROTOCOL'] : 'HTTP/1.1');

        $html = "\tFrom: {$remote_addr}{$remote_port}<br><br>\n";
        $html .= "\t{$method} {$path} {$ver}<br><br>\n";
        $html .= "\tRequest Headers:<br>\n<table>\n";
        $html .= "\t<tbody>\n";
        foreach ($headers as $key => $value) {
            $key = str_replace('-', '&#8209;', htmlspecialchars($key));
            if ($key == 'Cookie') {
                $kbytes = number_format(strlen($value) / 1024, 2);
                $cookie = explode(';', $value);
                $value = "<table>";
                foreach ($cookie as $kv) {
                    list($k, $v) = explode('=', $kv);
                    $k = htmlspecialchars($k);
                    $v = htmlspecialchars($v);
                    $value .= "<tr><td>{$k}</td><td>= {$v};</td></tr>";
                }
                $value .= "</table> ({$kbytes} KB)";
            } else {
                $value = htmlspecialchars($value);
            }
            $html .= "\t\t<tr><td>{$key}:</td><td>{$value}</td></tr>\n";
        }
        $html .= "\t</tbody>\n\t</table>\n";
        if (!empty($req['QUERY_STRING'])) {
            parse_str($req['QUERY_STRING'], $query);
            ksort($query);
            $html .= "<br>\n";
            $html .= "\tQuery String Parameters:<br>\n<table>\n";
            $html .= "\t<tbody>\n";
            foreach ($query as $key => $value) {
                $key = str_replace('-', '&#8209;', htmlspecialchars($key));
                $value = htmlspecialchars($value);
                $html .= "\t\t<tr><td>{$key}</td><td>= {$value}</td></tr>\n";
            }
            $html .= "\t</tbody>\n\t</table>\n";
        }
        if (!empty($this->post)) {
            ksort($this->post);
            $html .= "<br>\n";
            $html .= "\tForm Data:<br>\n<table>\n";
            $html .= "\t<tbody>\n";
            foreach ($this->post as $key => $value) {
                $key = str_replace('-', '&#8209;', htmlspecialchars($key));
                $value = htmlspecialchars($value);
                $html .= "\t\t<tr><td>{$key}</td><td> = {$value}</td></tr>\n";
            }
            $html .= "\t</tbody>\n\t</table>\n";
        }

        return $html;
    }

    public function getLabel() {
        return 'request';
    }
}

