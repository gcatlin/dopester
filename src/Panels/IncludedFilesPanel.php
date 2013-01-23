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

class IncludedFilesPanel extends Panel {
    protected $base_dir = '';
    protected $includes = '';

    public function __construct($base_dir='') {
        $this->base_dir = $base_dir . (substr($base_dir, -1) == '/' ? '' : '/');
        $this->includes = get_included_files();
    }

    public function getId() {
        return 'includes';
    }

    public function getHtml() {
        $includes    = $this->includes;
        $constants   = get_defined_constants(true);
        $functions   = get_defined_functions();
        $classes     = get_declared_classes();
        $interfaces  = get_declared_interfaces();
        $autoloaders = (array) spl_autoload_functions();
        foreach ($autoloaders as $i => $autoloader) {
            if (is_array($autoloader)) {
                $class = $autoloader[0];
                $class = (is_object($class) ? get_class($class) : $class);
                $autoloaders[$i] = $class . '::' . $autoloader[1];
            }
        }

        $total_size = 0;
        $sizes = array();
        foreach ($includes as $file) {
            $size = filesize($file);
            $sizes[$file] = $size;
            $total_size += $size;
        }
        $max_kb = number_format(max($sizes) / 1024, 2);
        $total_kb = number_format($total_size/1024);

        $stats = array(
            'Included Files' => count($includes) . " ({$max_kb} KB max; {$total_kb} KB total)",
            'Include Path'   => explode(PATH_SEPARATOR, get_include_path()),
            'Constants'      => (isset($constants['user']) ? count($constants['user']) : 0),
            'Functions'      => count($functions['user']),
            'Classes'        => count($classes) - 150, // predfined classes
            'Interfaces'     => count($interfaces) - 12, // predefined interfaces
            'Autoloaders'    => implode(', ', $autoloaders),
        );

        $html = "\t<table>\n";
        $html .= "\t<tbody>\n";
        foreach ($stats as $key => $value) {
            $key = htmlspecialchars($key);
            if (is_array($value)) {
                array_map('htmlspecialchars', $value);
                $value = implode('<br>', $value);
            } else {
                $value = htmlspecialchars($value);
            }
            $html .= "\t\t<tr><td>{$key}:</td><td>{$value}</td></tr>\n";
        }
        $html .= "\t</tbody>\n\t</table><br>\n";

        // @TODO make sortable
        $total = 0;
        sort($includes);

        $html .= "\tIncluded Files<br>\n<table>\n";
        $html .= "\t<thead><tr><td>#</td><td>path</td><td>size</td></tr></thead>\n";
        $html .= "\t<tfoot><tr><td></td><td></td><td class=\"sum\"></td></tr></tfoot>\n";
        $html .= "\t<tbody>\n";
        foreach ($includes as $i => $file) {
            $path = $file;
            if ($this->base_dir != '/') {
                $path = str_replace($this->base_dir, '', $path);
            }
            $size = number_format($sizes[$file] / 1024, 1) . ' KB';
            $css = (($i+1) % 2 == 0 ? 'even' : 'odd');
            $html .= "\t\t<tr class=\"{$css}\"><td>".($i+1)."</td><td>{$path}</td><td align=\"right\">{$size}</td></tr>\n";
        }
        $html .= "\t</tbody>\n\t</table>\n";

        return $html;
    }

    public function getLabel() {
        return count($this->includes) . ' includes';
    }
}

