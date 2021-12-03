<?php
/**
 * 注册要启动的任务
 */
namespace WolfansSm\Library\Http\App;

use WolfansSm\Library\Http\Tool\Tool;
use WolfansSm\Library\Share\Table;

class Route {
    protected $allHttpPosts;
    protected $ipList;

    public function __construct($allPort, $ipList) {
        $this->allHttpPosts = $allPort;
        $this->ipList       = $ipList;
    }

    public function index($route, $request) {
        if ($route == '/json') {
            return $this->json();
        } else {
            return $this->table();
        }
    }

    public function json() {
        $data        = [];
        $pStatusList = Tool::stats();//补充实时cpu 虚拟内存，实内存
        foreach (Table::getShareSchedule() as $options) {
            $routeId = $options['route_id'];
            $options = isset($pStatusList[$routeId]) ? array_merge($pStatusList[$routeId], $options) : $options;
            $data[]  = $options;
        }
        return json_encode($data);
    }

    public function table() {
        $task = [];
        foreach ($this->ipList as $ip) {
            foreach ($this->allHttpPosts as $port) {
                $json = @file_get_contents('http://' . $ip . ':' . $port . '/json');
                $arr  = @json_decode($json, true);
                is_array($arr) && $task[$ip . ':' . $port] = $arr;
            }
        }

        $html = '
            <style type="text/css">
            table.gridtable {
                font-family: verdana,arial,sans-serif;
                font-size:11px;
                color:#333333;
                border-width: 1px;
                border-color: #666666;
                border-collapse: collapse;
            }
            table.gridtable th {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                background-color: #dedede;
            }
            table.gridtable td {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                background-color: #ffffff;
            }
            </style>';
        $html .= '<table class="gridtable">';
        $html .= '<tr> <th>节点</th><th>任务</th><th>计划时间</th> <th>最大</th><th>最小</th><th>loop</th><th>sleep</th> 
                        <th>历史启动</th><th>总/平均时间</th><th>最近运行时间</th><th>运行量</th> <th>最大CPU（实时）</th> <th>最大VSZ（实时）</th> <th>最大RSS（实时）
                        <th>日志总条数/大小（实时）</th> 
                  </tr>';
        foreach ($task as $ip => $schedule) {
            foreach ($schedule as $options) {
                $aa   = $options['history_exec_num'] ? $options['history_exec_num'] : 1;
                $html .= '<tr> <td>' .
                    $ip . '</td> <td>' .
                    $options['route_id'] . '</td> <td>' .
                    $options['crontab'] . '</td> <td>' .
                    $options['max_pnum'] . '</td><td>' .
                    $options['min_pnum'] . '</td><td>' .
                    $options['loopnum'] . '</td><td>' .
                    $options['loopsleepms'] . '</td><td>' .
                    $options['history_exec_num'] . '</td><td>' .
                    Tool::getNum($options['all_exec_time']) . '/' . Tool::getNum(intval($options['all_exec_time'] / $aa)) . '</td><td>' .
                    date('Y-m-d H:i:s', $options['last_exec_time']) . '</td><td>' .
                    $options['current_exec_num'] . '</td><td>' .
                    ($options['cpu'] ?? '') . '</td><td>' .
                    (Tool::getByte($options['vsz']) ?? '') . '</td><td>' .
                    (Tool::getByte($options['rss']) ?? '') . '</td><td>' .
                    (Tool::getNum($options['log_num'] + $options['log_tmp_num']) . '/' . Tool::getByte($options['log_len'] + $options['log_tmp_len'])) . '</td></tr>';
            }
        }
        $html .= '</table>';
        return $html;
    }
}