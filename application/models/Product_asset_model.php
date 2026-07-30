  <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_asset_model extends MY_Model {
    protected $table = 'product_assets';

    public function insert($data) {
        $data['is_deleted'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function soft_delete($id) { 
        return $this->update($id, ['is_deleted' => 1]); 
    }

    public function get_with_product($id) {
        return $this->db->select('a.*, p.name as product_name')
            ->from('product_assets a')
            ->join('products p', 'p.id = a.product_id', 'left')
            ->where('a.id', $id)
            ->where('a.is_deleted', 0)
            ->get()->row_array();
    }

    public function datatable($params) {
        $this->db->select('a.id, a.title, a.asset_type, p.name as product_name, a.file_link, a.created_at, a.updated_at')
            ->from('product_assets a')
            ->join('products p', 'p.id = a.product_id', 'left')
            ->where('a.is_deleted', 0);

        $search = $params['search']['value'] ?? '';
        if ($search) {
            $this->db->group_start()
                ->like('a.title', $search)
                ->or_like('p.name', $search)
                ->or_like('a.asset_type', $search)
                ->group_end();
        }
        
        $type = $params['type'] ?? '';
        if ($type && $type !== 'All') {
            if ($type === 'Documents') {
                $this->db->where_in('a.asset_type', ['Document', 'Brochure']);
            } elseif ($type === 'Videos') {
                $this->db->where('a.asset_type', 'Video');
            } elseif ($type === 'Images') {
                $this->db->where('a.asset_type', 'Image');
            } elseif ($type === 'Presentations') {
                $this->db->where_in('a.asset_type', ['PPT', 'Presentation']);
            } else {
                $this->db->where('a.asset_type', $type);
            }
        }

        $total = $this->db->count_all_results('', false);
        
        $order_cols = ['a.id', 'a.title', 'a.asset_type', 'p.name', 'a.file_link', 'a.created_at'];
        $oi = $params['order'][0]['column'] ?? 0;
        $od = $params['order'][0]['dir'] ?? 'desc';
        $this->db->order_by($order_cols[$oi] ?? 'a.id', $od);
        
        $this->db;
        if (isset($params['length']) && $params['length'] != -1) {
            $this->db->limit($params['length'], $params['start'] ?? 0);
        }
        return [$this->db->get()->result_array(), $total];
    }
    
    public function get_counts() {
        $counts = [
            'All' => 0,
            'Documents' => 0,
            'Videos' => 0,
            'Images' => 0,
            'Presentations' => 0
        ];
        
        $res = $this->db->select('asset_type, COUNT(id) as cnt')
            ->from('product_assets')
            ->where('is_deleted', 0)
            ->group_by('asset_type')
            ->get()->result_array();
            
        foreach ($res as $r) {
            $counts['All'] += $r['cnt'];
            if (in_array($r['asset_type'], ['Document', 'Brochure'])) $counts['Documents'] += $r['cnt'];
            elseif ($r['asset_type'] === 'Video') $counts['Videos'] += $r['cnt'];
            elseif ($r['asset_type'] === 'Image') $counts['Images'] += $r['cnt'];
            elseif (in_array($r['asset_type'], ['PPT', 'Presentation'])) $counts['Presentations'] += $r['cnt'];
        }
        
        return $counts;
    }
}
