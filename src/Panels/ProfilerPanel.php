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

abstract class ProfilerPanel extends Panel {
    protected $commands = array();
    protected $elapsed_ms;
    protected $profiler;

    public function __construct($profiler) {
        $this->profiler = $profiler;

        foreach ($profiler->getLog() as $event) {
            $cmd = $event[0];
            if (!isset($this->commands[$cmd])) {
                $this->commands[$cmd] = 0.0;
            }
            $this->commands[$cmd] += $event[3][1] - $event[3][0];
        }
        $this->elapsed_ms = array_sum($this->commands) * 1000;
    }

    public function getId() {
        return get_class($this->profiler);
    }
}


