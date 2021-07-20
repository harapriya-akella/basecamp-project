<?php

class BZ_Model extends CI_Model {

    function get($table, $where = '', $select = '', $perpage = 0, $start = 0, $order_by = '') {
        if ($select) {
            $this->db->select($select);
        }
        $this->db->from($table);
        if ($perpage != 0 && $perpage != NULL)
            $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }
        if ($order_by) {
            $this->db->order_by($order_by);
        }
        $query = $this->db->get()->result();
        if (!empty($query))
            if ($perpage != 0 && $perpage != NULL)
                $result = $query;
            else
                $result = $query[0];
        else
            $result = array();
        return $result;
    }

    function get_max($table, $column = '', $where = array()) {
        $this->db->select_max($column);
        $this->db->where($where);
        $query = $this->db->get($table)->row();
        if ($query) {
            return $query;
        } else {
            return FALSE;
        }
    }

    function getCount($table, $where = '') {

        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results();
    }

    function add($table, $data) {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == 1) {
            return $this->db->insert_id();
        }

        return FALSE;
    }

    function edit($table, $data, $fieldID, $ID) {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);
        if ($this->db->affected_rows()) {
            return TRUE;
        }

        return FALSE;
    }

    function deleteThis($table, $fieldID, $ID) {
        $this->db->where($fieldID, $ID);
        //$this->db->update($table, array('is_deleted' => 1, 'modified_user_id' => $this->session->userdata('user_id'), 'modified_user_ip' => getUserIpAddr(), 'modified_date' => date('Y-m-d H:i:s')));
        $this->db->update($table, array('is_deleted' => 1, 'modified_user_id' => 1, 'modified_user_ip' => getUserIpAddr(), 'modified_date' => date('Y-m-d H:i:s')));
        if ($this->db->affected_rows() == 1) {
            return TRUE;
        }

        return FALSE;
    }

    function deleteReal($table, $fieldID, $ID) {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return TRUE;
        }

        return FALSE;
    }

    function count($table) {
        return $this->db->count_all($table);
    }

    function getDropDownEntire($table, $col1, $col2, $where = '', $with_empty = false) {
        $this->db->select($col1 . ',' . $col2);
        $this->db->from($table);
        if ($where) {
            $this->db->where_in($where[0], $where[1]);
        }

        $query = $this->db->get();
        if (!empty($query)) {
            if ($with_empty)
                $result_arr = array();
            else
                $result_arr = array("" => "Select Any");
            $results = $query->result();
            foreach ($results as $result) {
                $result_arr[$result->$col1] = $result->$col2;
            }
            return $result_arr;
        } else
            return array("" => "Not Available");
    }

    function getDropDownWithout($table, $col1, $col2, $where = '', $with_empty = false) {
        $this->db->select($col1 . ',' . $col2);
        $this->db->from($table);
        $this->db->where(array('is_deleted' => '0'));
        if ($where) {
            $this->db->where_in($where[0], $where[1]);
        }

        $query = $this->db->get();
        if (!empty($query)) {
            if ($with_empty)
                $result_arr = array();
            else
                $result_arr = array("" => "Select Any");
            $results = $query->result();
            foreach ($results as $result) {
                $result_arr[$result->$col1] = $result->$col2;
            }
            return $result_arr;
        } else
            return array("" => "Not Available");
    }

    function getDropDownAuto($table, $col1, $col2, $where = '') {
        $this->db->select($col1 . ',' . $col2);
        $this->db->from($table);
        if ($where) {
            $this->db->where_in($where[0], $where[1]);
        }

        $query = $this->db->get();
        if (!empty($query)) {
            $result_arr = array();
            $results = $query->result();
            foreach ($results as $result) {
                $result_arr[] = array('id' => $result->$col1, 'label' => $result->$col2, 'value' => $result->$col2);
            }
            return $result_arr;
        } else
            return array();
    }

    function getDropDown($table, $col1, $col2, $where = '', $is_deleted = false, $is_select = true) {
        $this->db->select($col1 . ',' . $col2);
        $this->db->from($table);
        if ($is_deleted) {
            $this->db->where('is_deleted', '0');
        }
        if ($where) {
            $this->db->where_in($where[0], $where[1]);
        }

        $query = $this->db->get();
        if (!empty($query)) {
            if ($is_select)
                $result_arr = array("" => "Select Any");
            $results = $query->result();
            foreach ($results as $result) {
                $result_arr[$result->$col1] = $result->$col2;
            }
            return $result_arr;
        } else
            return array("" => "Not Available");
    }

    function check_unique($table, $where = array()) {
        $query = $this->db->limit(1)->get_where($table, $where);
        return $query->num_rows() === 0;
    }

    function getAll($table, $where = '', $order_by = '', $select = '') {
        if ($select)
            $this->db->select($select);
        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }
        if ($order_by) {
            $this->db->order_by($order_by);
        }
        $query = $this->db->get()->result();
        if (!empty($query)) {
            $result = $query;
        } else {
            $result = array();
        }
        return $result;
    }

    function getDropDownMaker($table, $col1, $col2, $where, $is_deleted = false, $is_select = true) {

        $this->db->select($col1 . ',' . $col2);
        $this->db->from($table);
        if ($is_deleted) {
            $this->db->where('is_deleted', '0');
        }
        if ($where) {
            $this->db->where($where);
        }
        $query = $this->db->get();
        if (!empty($query)) {
            if ($is_select)
                $result_arr = array("" => "Select Any");
            $results = $query->result();
            foreach ($results as $result) {
                $result_arr[$result->$col1] = $result->$col2;
            }
            return $result_arr;
        } else
            return array("" => "Not Available");
    }

    function getNameIdSeq($table, $columnName1, $columnName2, $where) {
        $this->db->select($table . '.' . $columnName1 . ',' . $table . '.' . $columnName2);
        $this->db->from($table);

        if ($where) {
            $this->db->where($where);
        }
        $this->db->group_by($table . '.' . $columnName1);
        $this->db->order_by($table . '.' . $columnName2, 'ASC');
        return $this->db->get()->result_array();
    }

    function get_array($table, $where = '') {
        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }
        $query = $this->db->get()->result_array();
        if (!empty($query))
            return $query;
        else
            return FALSE;
    }
}
