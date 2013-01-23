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

// @TODO make sure the location header shows up as part of the response in the toolbar
// @TODO handle when headers were already sent (e.g. via a callback)
class RedirectInterceptor {
    private static $instance;
    private $enabled = false;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new RedirectInterceptor();
        }
        return self::$instance;
    }

    private function __construct() {
        // singleton
    }

    public function enable() {
        if (!$this->enabled) {
            register_shutdown_function(array($this, 'intercept'));
            ob_start();
            $this->enabled = true;
        }
    }

    public function getLocationUrl($headers) {
        foreach ($headers as $header) {
            if (($header[0] == 'L' || $header[0] == 'l') && stripos($header, 'location:') === 0) {
                list($_, $url) = explode(':', $header, 2);
                return trim($url);
            }
        }
    }

    public function intercept() {
        if (!headers_sent()) {
            $url = $this->getLocationUrl(headers_list());
            if ($url) {
                // disable the redirect by clearing the header
                header('Location:');

                // provide a link to follow the redirect
                $url_enc = htmlspecialchars($url);
                printf("<h1>Redirect Intercepted</h1><p>Continue to: <a href=\"%s\">%s</a></p>\n", $url_enc, $url_enc);
            }
        }
        ob_end_flush();
    }
}

