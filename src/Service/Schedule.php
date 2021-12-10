<?php

namespace WolfansSmWb\Service;

use \WolfansSmWb\Model\Schedule as ScheduleModel;

class Schedule {
    protected $scheduleModel;

    public function __construct($options) {
        $this->scheduleModel = new ScheduleModel($options);
    }

    public function get($params) {
        $nodeIds = isset($params['nodes']) ? $params['nodes'] : [];
        $where   = [];
        if ($nodeIds) {
            $where['node[!]'] = $nodeIds;
        }
        list($list, $count) = $this->scheduleModel->getListCount($where, '*', ['id' => 'DESC']);
        return ['list' => $list, 'total_count' => $count];
    }

    public function edit() {
    }

    public function editBatch($params) {
        $node         = isset($params['node']) ? $params['node'] : '';
        $scheduleList = isset($params['schedule_list']) ? $params['schedule_list'] : '';
        $scheduleList = @json_decode($scheduleList, true);
        $scheduleList = is_array($scheduleList) ? $scheduleList : [];
        if (!$node || !$scheduleList) {
            return;
        }
        $existList = $this->scheduleModel->getList(['node' => $node], '*');
        $existList = array_column($existList, null, 'schedule');
        var_dump($scheduleList);
        foreach ($scheduleList as $scheduleItem) {
            $schedule = isset($scheduleItem['schedule']) ? $scheduleItem['schedule'] : '';
            if (!$schedule) {
                continue;
            }
            $config  = isset($scheduleItem['config']) ? json_decode($scheduleItem['config']) : [];
            $runInfo = isset($scheduleItem['run_info']) ? json_decode($scheduleItem['run_info']) : [];
            $config  = is_array($config) ? $config : [];
            $runInfo = is_array($runInfo) ? $runInfo : [];
            if (isset($existList[$schedule])) {
                $this->updateSchedule($node, $schedule, $config, $runInfo);
            } else {
                $this->insertSchedule($node, $schedule, $config, $runInfo);
            }
        }
    }

    protected function updateSchedule($node, $schedule, $config = [], $runInfo = []) {
        $where = [
            'node'     => $node,
            'schedule' => $schedule,
        ];
        $set   = [
            'config'     => json_encode($config),
            'run_info'   => json_encode($runInfo),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        var_dump($where, $set);
        $res = $this->scheduleModel->edit($set, $where);
        var_dump($res);
    }

    protected function insertSchedule($node, $schedule, $config = [], $runInfo = []) {
        $insert = [
            'node'       => $node,
            'schedule'   => $schedule,
            'config'     => json_encode($config),
            'run_info'   => json_encode($runInfo),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $addId  = $this->scheduleModel->add($insert);
    }
}