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

class Toolbar {
    private $panels = array();
    private $is_xmlhttprequest;

    public static function register($start_time = null, $base_dir = null, $intercept_redirects = true, $allowed_addrs = null) {
        $start_time = ($start_time ? $start_time : microtime(true));

        if ($intercept_redirects) {
            RedirectInterceptor::instance()->enable();
        }

        $enable_toolbar = true;
        if ($allowed_addrs) {
            $remote_addr = null;
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $remote_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                $remote_addr = $_SERVER['REMOTE_ADDR'];
            }

            if ($remote_addr !== null && !in_array($remote_addr, $allowed_addrs)) {
                $enable_toolbar = false;
            }
        }

        if ($enable_toolbar) {
            $toolbar = new Toolbar();
            $toolbar->addPanel(new ServerInfoPanel('system'));
            if (isset($_SESSION)) {
                $toolbar->addPanel(new SessionPanel($_SESSION));
            }
            $toolbar->addPanel(new RequestPanel($_SERVER, $_POST, $_FILES));
            $toolbar->addPanel(new IncludedFilesPanel($base_dir));
            foreach (Profiler::getRegisteredProfilers() as $profiler) {
                $toolbar->addPanel($profiler->createPanel());
            }

            register_shutdown_function(function () use ($start_time, $toolbar) {
                $elapsed = microtime(true) - $start_time;
                $toolbar->addPanel(new ElapsedTimePanel($elapsed));
                echo $toolbar->render();
            });

            return $toolbar;
        }
    }

    public function addPanel(Panel $panel) {
        $this->panels[] = $panel;
        return $this;
    }

    public function isXmlHttpRequest() {
        if ($this->is_xmlhttprequest === null) {
            $this->is_xmlhttprequest = (
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0
            );
        }
        return $this->is_xmlhttprequest;
    }

    public function render() {
        if ($this->isXmlHttpRequest()) {
            return;
        }

        $html = $this->_headerOutput() . "\n";
        $html .= "<div id='ZFDebugResize'></div>\n";
        $html .= "<div id='ZFDebug_info'>\n";
        // $html .= "\t<span class='ZFDebug_tab' style='padding-right:0px;' onclick='ZFDebugPanel(ZFDebugCurrent);'></span>\n";

        foreach ($this->panels as $panel) {
            $label = $panel->getLabel();
            $showPanel = ($panel->render() == '') ? 'log' : $panel->getId();
            $html .= "\t" . '<span id="ZFDebugInfo_' . $panel->getId()
                . '" class="ZFDebug_tab clickable" onclick="ZFDebugPanel(\'ZFDebug_'
                . $showPanel . '\');">';
            $html .= $label . "</span>\n";
        }

        // $html .= '<span id="ZFDebugInfo_Request" class="ZFDebug_tab">' . "\n"
        //     // . round(memory_get_peak_usage()/1024) . 'KB in '
        //     . $page_gen_ms . 'ms total'
        //     . '</span>' . "\n";

        $html .= "</div>\n";

        foreach ($this->panels as $panel) {
            $panel_html = $panel->render();
            if (!$panel_html) {
                continue;
            }

            $html .= "\n"
                . '<div id="ZFDebug_' . $panel->getId() . '" class="ZFDebug_panel" name="ZFDebug_panel">' . "\n"
                . $panel_html
                . "</div>\n";
        }

        return "<div id='ZFDebug_offset'></div>\n<div id='ZFDebug'>\n{$html}\n</div>\n</body>";
    }

    protected function _headerOutput() {
        $collapsed = isset($_COOKIE['ZFDebugCollapsed']) ? $_COOKIE['ZFDebugCollapsed'] : '';
        if ($collapsed) {
            $boxheight = isset($_COOKIE['ZFDebugHeight']) ? $_COOKIE['ZFDebugHeight'] : '400';
        } else {
            $boxheight = '32';
        }

        // @TODO use heredoc/nowdoc
        return '
<style type="text/css" media="screen">
    html,body {height:100%;}
    #ZFDebug, #ZFDebug div, #ZFDebug span, #ZFDebug h1, #ZFDebug h2, #ZFDebug h3, #ZFDebug h4, #ZFDebug h5, #ZFDebug h6, #ZFDebug p, #ZFDebug blockquote, #ZFDebug pre, #ZFDebug a, #ZFDebug code, #ZFDebug em, #ZFDebug img, #ZFDebug strong, #ZFDebug dl, #ZFDebug dt, #ZFDebug dd, #ZFDebug ol, #ZFDebug ul, #ZFDebug li, #ZFDebug table, #ZFDebug tbody, #ZFDebug tfoot, #ZFDebug thead, #ZFDebug tr, #ZFDebug th, #ZFDebug td {margin: 0; padding: 0; border: 0; outline: 0; font-size: 100%; vertical-align: baseline; background: transparent;}
    #ZFDebug_offset {height:'.$boxheight.'px;}
    #ZFDebug {height:'.$boxheight.'px; width:100%; background:#262626; font: 12px/1.4em Lucida Grande, Lucida Sans Unicode, sans-serif; position:fixed; bottom:0px; left:0px; color:#FFF; z-index:2718281828459045;}
    #ZFDebug p {margin:1em 0}
    #ZFDebug a {color:#FFFFFF}
    #ZFDebug tr {color:#FFFFFF;}
    #ZFDebug tr.row:hover{background-color:#999;}
    #ZFDebug th {text-align:left; padding:2px 4px;}
    #ZFDebug td {vertical-align:top; padding:2px 4px;}
    #ZFDebug ol {margin:1em 0 0 0; padding:0; list-style-position: inside;}
    #ZFDebug li {margin:0;}
    #ZFDebug .clickable {cursor:pointer}
    #ZFDebug #ZFDebug_info {display:block; height:32px; border-top: 1px solid
    ##1a1a1a; border-bottom: 1px solid #1a1a1a; background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFBAMAAAB/QTvWAAAAG1BMVEUmJiYwMDDd7h7d7h7d7h7d7h4wMDAzMzM2Njb/0/kjAAAAGklEQVQImWNIb2BgqGBIYGhgK2BgAHLYKhgALDkEG+/EMAoAAAAASUVORK5CYII=);}
    #ZFDebug #ZFDebugResize {cursor:row-resize; height:2px;border-bottom:1px solid #1a1a1a; }
    #ZFDebug .ZFDebug_tab {border-left: 1px solid black; padding:0 15px; line-height:32px; display:block; float:right}
    #ZFDebug .ZFDebug_panel {border-top: 1px solid #333; padding:5px 15px 15px 15px; font: 11px/1.4em Menlo, Monaco, Lucida Console, monospace; text-align:left; height:'.($boxheight-50).'px; overflow:auto; display:none;}
    #ZFDebug h4 {font:bold 12px/1.4em Menlo, Monaco, Lucida Console, monospace; margin:1em 0;}
    #ZFDebug .ZFDebug_active {background:#1a1a1a;}
    #ZFDebug .ZFDebug_panel .pre {margin:0 0 0 22px}
    #ZFDebug_exception { border:1px solid #CD0A0A;display: block; }
</style>
<script type="text/javascript">
    var ZFDebugLoad = window.onload;
    window.onload = function(){
        if ("'.$collapsed.'" != "") {
            ZFDebugPanel("' . $collapsed . '");
        }
        window.zfdebugHeight = "'.(isset($_COOKIE['ZFDebugHeight']) ? $_COOKIE['ZFDebugHeight'] : '240').'";

        document.onmousemove = function(e) {
            var event = e || window.event;
            window.zfdebugMouse = Math.max(40, Math.min(window.innerHeight, -1*(event.clientY-window.innerHeight-32)));
        }

        var ZFDebugResizeTimer = null;
        document.getElementById("ZFDebugResize").onmousedown=function(e){
            ZFDebugResize();
            ZFDebugResizeTimer = setInterval("ZFDebugResize()",50);
            return false;
        }
        document.onmouseup=function(e){
            clearTimeout(ZFDebugResizeTimer);
        }
    };

    function ZFDebugResize() {
        window.zfdebugHeight = window.zfdebugMouse;
        document.cookie = "ZFDebugHeight="+window.zfdebugHeight+";expires=;path=/";
        document.getElementById("ZFDebug").style.height = window.zfdebugHeight+"px";
        document.getElementById("ZFDebug_offset").style.height = window.zfdebugHeight+"px";

        var panels = document.getElementById("ZFDebug").children;
        for (var i=0; i < document.getElementById("ZFDebug").childElementCount; i++) {
            if (panels[i].className.indexOf("ZFDebug_panel") == -1)
                continue;

            panels[i].style.height = window.zfdebugHeight-50+"px";
        }
    }

    var ZFDebugCurrent = null;

    function ZFDebugPanel(name) {
        if (ZFDebugCurrent == name) {
            document.getElementById("ZFDebug").style.height = "32px";
            document.getElementById("ZFDebug_offset").style.height = "32px";
            ZFDebugCurrent = null;
            document.cookie = "ZFDebugCollapsed=;expires=;path=/";
        } else {
            document.getElementById("ZFDebug").style.height = window.zfdebugHeight+"px";
            document.getElementById("ZFDebug_offset").style.height = window.zfdebugHeight+"px";
            ZFDebugCurrent = name;
            document.cookie = "ZFDebugCollapsed="+name+";expires=;path=/";
        }

        var panels = document.getElementById("ZFDebug").children;
        for (var i=0; i < document.getElementById("ZFDebug").childElementCount; i++) {
            if (panels[i].className.indexOf("ZFDebug_panel") == -1)
                continue;

            if (ZFDebugCurrent && panels[i].id == name) {
                document.getElementById("ZFDebugInfo_"+name.substring(8)).className += " ZFDebug_active";
                panels[i].style.display = "block";
                panels[i].style.height = (window.zfdebugHeight-50)+"px";
            } else {
                var element = document.getElementById("ZFDebugInfo_"+panels[i].id.substring(8));
                element.className = element.className.replace("ZFDebug_active", "");
                panels[i].style.display = "none";
            }
        }
    }
</script>';
    }
}

