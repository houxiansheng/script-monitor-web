<?php
/*!
 * Medoo database framework
 * https://medoo.in
 * Version 1.7.10
 *
 * Copyright 2020, Angel Lai
 * Released under the MIT license
 */

namespace WolfansSmWb\Model;

use WolfansSmWb\Library\DB\Medoo;

class Base {
    protected $table = '';
    protected $Medoo;

    public function __construct(array $options) {
        $this->Medoo = new Medoo($options);
    }

    public function getList($where, $fields, $orderBy = [], $limit = []) {
        $orderBy && $where['ORDER'] = $orderBy;
        $limit && $where['LIMIT'] = $limit;
        return $this->Medoo->select($this->table, $fields, $where);
    }

    public function count($where) {
        return $this->Medoo->count($this->table, $where);
    }

    public function getListCount($where, $fields, $orderBy, $limit = []) {
        $list  = $this->getList($where, $fields, $orderBy, $limit);
        $count = $this->count($where);
        return [$list, $count];
    }

    public function edit($set, $where) {
        $state = $this->Medoo->update($this->table, $set, $where);
        return $state->rowCount();
    }

    public function add($data) {
        $this->Medoo->insert($this->table, $data);
        return $this->Medoo->id();
    }
}