<?php

namespace WolfansSmWb\Service;

use WolfansSmWb\Service\Schedule as ScheduleService;
use \WolfansSmWb\Model\Node as NodeModel;

class Node {
    protected $nodeModel;
    protected $dBoptions;

    public function __construct($dBoptions) {
        $this->dBoptions = $dBoptions;
        $this->nodeModel = new NodeModel($dBoptions);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function get($params) {
        $activeSt = isset($params['active_st']) ? $params['active_st'] : (time() - 86400);
        $nodeIds  = isset($params['node_uuids']) ? $params['node_uuids'] : [];
        $where    = [];
        if ($activeSt) {
            $where['heartbeat_at[>]'] = $activeSt;
        }
        if ($nodeIds) {
            $where['id[!]'] = $nodeIds;
        }
        list($list, $count) = $this->nodeModel->getListCount($where, '*', ['heartbeat_at' => 'DESC']);
        return ['list' => $list, 'total_count' => $count];
    }

    /**
     * 编辑操作
     *
     * @param $params
     */
    public function edit($params) {
        $node       = isset($params['node']) ? $params['node'] : '';
        $serverInfo = isset($params['server_info']) ? $params['server_info'] : '';
        $serverInfo = is_array($serverInfo) ? $serverInfo : [];
        if (!$node) {
            return;
        }
        $where     = ['node' => $node];
        $existList = $this->nodeModel->getList($where, '*');
        $existList = array_column($existList, null, 'node');
        if ($existList) {
            $where = ['node' => $node];
            $set   = [
                'server_info' => json_encode($serverInfo),
                'updated_at'  => date('Y-m-d H:i:s')
            ];
            $this->nodeModel->edit($set, $where);
        } else {
            $data = [
                'node'        => $node,
                'server_info' => json_encode($serverInfo),
                'created_at'  => date('Y-m-d H:i:s')
            ];
            $this->nodeModel->add($data);
        }
    }

    /**
     * 获取所有数据
     *
     * @return array
     */
    public function getAll() {
        $params      = ['active_st' => time() - 60];
        $allNodeList = $this->get($params);
        $nodeIds     = array_column($allNodeList['list'], 'id');
        if ($nodeIds) {
            $scheduleService = new ScheduleService($this->dBoptions);
            $scheduleList    = $scheduleService->get(['nodes' => $nodeIds]);
            var_dump($scheduleList);
        } else {
            $scheduleList = ['list' => [], 'total_count' => 0];;
        }
        $data = [];
        foreach ($allNodeList['list'] as $node) {
            $data[$node['node']] = [
                'node'     => $node,
                'schedule' => []
            ];
        }
        foreach ($scheduleList['list'] as $item) {
            $nodeId = $item['node'];
            if (isset($data[$nodeId])) {
                $data[$nodeId]['schedule'][] = $item;
            }
        }
        return $data;
    }
}