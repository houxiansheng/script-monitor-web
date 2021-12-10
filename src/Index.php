<?php

namespace WolfansSmWb;

use WolfansSmWb\Service\Node as NodeService;
use WolfansSmWb\Service\Schedule as ScheduleService;

class  Index {
    protected $dbOption;

    public function __construct($dbOption) {
        $this->dbOption = $dbOption;
    }

    /**
     * 初始化数据
     */
    public function init() {

    }

    public function exec($params) {
        $action = isset($params['action']) ? $params['action'] : '';
        $data   = isset($params['data']) ? json_decode($params['data'], true) : '';
        $data   = is_array($data) ? $data : [];
        if (!$action || !method_exists($this, $action)) {
            return;
        }
        return $this->$action($data);
    }

    /**
     * 所有数据
     */
    protected function getAll($params) {
        $NodeService = new NodeService($this->dbOption);
        $res         = $NodeService->getAll();
        return $res;
    }

    /**
     * 获取节点信息
     */
    protected function getNode($params) {

    }

    /**
     * 获取计划任务表
     */
    protected function getSchedule($nodeId = null, $scheduleId = null) {
    }

    /**
     * 更新节点信息
     */
    protected function updateNode($params) {
        $NodeService     = new NodeService($this->dbOption);
        $res             = $NodeService->edit($params);
        $scheduleService = new ScheduleService($this->dbOption);
        var_dump($scheduleService);
        $res = $scheduleService->editBatch($params);
        return $res;
    }
}

