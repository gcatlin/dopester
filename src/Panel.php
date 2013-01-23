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

abstract class Panel {
    protected $id;
    protected $cached_html;

    public function getId() {
        if ($this->id === null) {
            $this->id = uniqid();
        }
        return $this->id;
    }

    abstract public function getLabel();
    abstract public function getHtml();

    public function render() {
        if ($this->cached_html === null) {
            $this->cached_html = $this->getHtml();
        }
        return $this->cached_html;
    }

    protected function _cleanData($values) {
        if (is_array($values)) {
            ksort($values);
        }

        $retVal = '<div class="pre">';
        foreach ($values as $key => $value) {
            $key = htmlspecialchars($key);
            if (is_numeric($value)) {
                $retVal .= $key.' =&gt; '.$value.'<br>';
            } elseif (is_string($value)) {
                $retVal .= $key.' =&gt; \''.htmlspecialchars($value).'\'<br>';
            } elseif (is_array($value)) {
                $retVal .= $key.' =&gt; '.self::_cleanData($value);
            } elseif (is_object($value)) {
                $retVal .= $key.' =&gt; &lt;'.get_class($value).'&gt;<br>';
            } elseif (is_null($value)) {
                $retVal .= $key.' =&gt; NULL<br>';
            }
        }
        return $retVal.'</div>';
    }
}

