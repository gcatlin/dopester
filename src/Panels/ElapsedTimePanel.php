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

class ElapsedTimePanel extends Panel {
    protected $elapsed;

    public function __construct($elapsed) {
        $this->elapsed = $elapsed;
    }

    public function getId() {
        return 'elapsed';
    }

    public function getHtml() {
        return '';
    }

    public function getLabel() {
        return ($this->elapsed < 0.0005 ? '< 1' : number_format($this->elapsed * 1000)) . 'ms total';
    }
}
