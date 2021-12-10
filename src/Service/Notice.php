<?php

namespace WolfansSmWb\Service;

use WolfansSmWb\Service\Node as NodeService;
use WolfansSmWb\Service\Schedule as ScheduleService;
use \WolfansSmWb\Model\Node as NodeModel;

class Notice {
    protected $nodeService;
    protected $scheduleService;
    protected $nodeModel;

    public function __construct($options) {
        $this->nodeService     = new NodeService($options);
        $this->scheduleService = new ScheduleService($options);
        $this->nodeModel       = new NodeModel($options);
    }

    public function heartBeat($params) {
        $node = isset($params['node']) ? $params['node'] : '';
        if (!$node) {
            return false;
        }
        $set   = [
            'heartbeat_at' => time()
        ];
        $where = [
            'node' => $node
        ];
        $this->nodeModel->edit($set, $where);
        return true;
    }

    public function status($params) {
        $node = isset($params['node']) ? $params['node'] : '';
        if (!$node) {
            return;
        }
        $editRes = $this->nodeService->edit($params);
        $this->scheduleService->editBatch($params);
    }
}