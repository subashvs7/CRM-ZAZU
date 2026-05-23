<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model {
    protected $table = 'users';

    public function get_with_team($id) {
        return $this->db->select('u.*, t.name AS team_name')
            ->from('users u')
            ->join('teams t', 't.id = u.team_id', 'left')
            ->where(['u.id' => $id, 'u.is_deleted' => 0])
            ->get()->row_array();
    }

    public function get_staff_list($role = null) {
        $this->db->select('u.id, u.name, u.email, u.role, u.phone, u.team_id, t.name AS team_name, u.status')
            ->from('users u')
            ->join('teams t', 't.id = u.team_id', 'left')
            ->where('u.is_deleted', 0);
        if ($role) $this->db->where('u.role', $role);
        return $this->db->order_by('u.name')->get()->result_array();
    }

    public function get_field_staff($manager_id = null) {
        $this->db->select('u.id, u.name, u.email, u.phone, u.team_id, u.status')
            ->from('users u')
            ->where(['u.role' => 'field_staff', 'u.is_deleted' => 0]);
        if ($manager_id) {
            $teams = $this->db->where(['manager_id' => $manager_id, 'is_deleted' => 0])->get('teams')->result_array();
            $team_ids = array_column($teams, 'id');
            if ($team_ids) $this->db->where_in('u.team_id', $team_ids);
            else $this->db->where('u.id', 0);
        }
        return $this->db->order_by('u.name')->get()->result_array();
    }

    public function datatable($params, $status_filter = null) {
        $this->db->select('u.id, u.name, u.email, u.role, u.phone, t.name AS team_name, u.status, u.last_login_at, u.created_at')
            ->from('users u')
            ->join('teams t', 't.id = u.team_id', 'left');

        if ($status_filter === 'deleted')      $this->db->where('u.is_deleted', 1);
        elseif ($status_filter === 'active')   $this->db->where(['u.status' => 'active',   'u.is_deleted' => 0]);
        elseif ($status_filter === 'inactive') $this->db->where(['u.status' => 'inactive', 'u.is_deleted' => 0]);
        else                                   $this->db->where('u.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('u.name', $search)->or_like('u.email', $search)->or_like('u.phone', $search)
                ->group_end();
        }

        $total = $this->db->count_all_results('', false);

        $order_col = ['u.id','u.name','u.email','u.role','t.name','u.status','u.last_login_at'];
        $oi = $params['order'][0]['column'] ?? 0;
        $od = $params['order'][0]['dir']    ?? 'desc';
        $this->db->order_by($order_col[$oi] ?? 'u.id', $od);
        $this->db->limit($params['length'], $params['start']);

        $rows = $this->db->get()->result_array();
        return [$rows, $total];
    }

    public function update_password($id, $hash) {
        return $this->db->where('id', $id)->update('users', ['password' => $hash, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function get_permissions($user_id) {
        $rows = $this->db->where('user_id', $user_id)->get('user_permissions')->result_array();
        return array_column($rows, 'permission');
    }

    public function set_permissions($user_id, array $perms) {
        $this->db->where('user_id', $user_id)->delete('user_permissions');
        foreach ($perms as $p) {
            $this->db->insert('user_permissions', ['user_id' => $user_id, 'permission' => $p]);
        }
    }
}
