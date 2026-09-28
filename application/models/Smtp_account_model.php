<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Smtp_account_model extends MY_Model {
    protected $table = 'crm_smtp_accounts';

    public function __construct() {
        parent::__construct();
        $this->reset_daily_counters_if_needed();
    }

    /**
     * Automatically reset daily sent counters at midnight
     */
    public function reset_daily_counters_if_needed() {
        if (!$this->db->table_exists($this->table)) {
            return;
        }
        $today = date('Y-m-d');
        // Reset any accounts where last_reset_date is older than today
        $this->db->where('last_reset_date <', $today)
            ->where('is_deleted', 0)
            ->update($this->table, [
                'sent_today'      => 0,
                'last_reset_date' => $today,
                'status'          => 'active',
                'updated_at'      => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Get all active accounts in the pool
     */
    public function get_all_accounts() {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }
        $this->reset_daily_counters_if_needed();
        return $this->db->where('is_deleted', 0)
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    /**
     * Get the next available SMTP account that has not exceeded its daily limit
     * Sequential / Fair Load-Balancing rotation
     */
    public function get_next_available_account() {
        $this->reset_daily_counters_if_needed();

        return $this->db->where('is_deleted', 0)
            ->where('status', 'active')
            ->where('sent_today < daily_limit', null, false)
            ->order_by('sent_today', 'ASC') // Pick account with most remaining quota
            ->order_by('last_used_at', 'ASC')
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Increment sent counter for an SMTP account and mark limit_reached if threshold hit
     */
    public function increment_sent_count($account_id) {
        $account = $this->get_by_id($account_id);
        if (!$account) return false;

        $newSent = (int)$account['sent_today'] + 1;
        $limitReached = ($newSent >= (int)$account['daily_limit']);

        $updateData = [
            'sent_today'   => $newSent,
            'last_used_at' => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($limitReached) {
            $updateData['status'] = 'limit_reached';
        }

        $this->db->where('id', (int)$account_id)->update($this->table, $updateData);

        return [
            'account_id'    => $account_id,
            'sent_today'    => $newSent,
            'daily_limit'   => (int)$account['daily_limit'],
            'limit_reached' => $limitReached
        ];
    }

    /**
     * Get aggregated pool health status and alerts
     */
    public function get_pool_status() {
        $this->reset_daily_counters_if_needed();

        $accounts = $this->get_all_accounts();
        $totalCapacity = 0;
        $totalSent     = 0;
        $activeCount   = 0;
        $exhaustedCount = 0;
        $alerts        = [];

        foreach ($accounts as &$acc) {
            $cap  = (int)$acc['daily_limit'];
            $sent = (int)$acc['sent_today'];
            $rem  = max(0, $cap - $sent);

            $acc['remaining'] = $rem;
            $acc['percent_used'] = ($cap > 0) ? round(($sent / $cap) * 100) : 100;

            $totalCapacity += $cap;
            $totalSent     += $sent;

            if ($acc['status'] === 'active' && $rem > 0) {
                $activeCount++;
                if ($rem <= 10) {
                    $alerts[] = "Account '{$acc['name']}' ({$acc['sender_email']}) has only {$rem} emails left today!";
                }
            } else {
                $exhaustedCount++;
                if ($acc['status'] === 'limit_reached' || $rem === 0) {
                    $alerts[] = "Account '{$acc['name']}' ({$acc['sender_email']}) reached its daily limit of {$cap}. Auto-switched to remaining accounts.";
                }
            }
        }

        $totalRemaining = max(0, $totalCapacity - $totalSent);

        if ($activeCount === 0 && count($accounts) > 0) {
            $alerts[] = "CRITICAL: All " . count($accounts) . " Hostinger SMTP accounts have reached their daily limit! Further emails will be queued until tomorrow.";
        }

        return [
            'accounts'          => $accounts,
            'total_accounts'    => count($accounts),
            'active_accounts'   => $activeCount,
            'exhausted_count'   => $exhaustedCount,
            'total_capacity'    => $totalCapacity,
            'total_sent'        => $totalSent,
            'total_remaining'   => $totalRemaining,
            'percent_remaining' => ($totalCapacity > 0) ? round(($totalRemaining / $totalCapacity) * 100) : 0,
            'alerts'            => $alerts
        ];
    }

    /**
     * Save (insert or update) an SMTP account with max 10 accounts validation
     */
    public function save_account($data, $id = null) {
        $id = $id ? (int)$id : null;

        // If creating new, check max limit of 10 accounts
        if (!$id) {
            $currentCount = $this->db->where('is_deleted', 0)->count_all_results($this->table);
            if ($currentCount >= 10) {
                return [
                    'success' => false,
                    'message' => 'Maximum pool capacity reached! You can have up to 10 Hostinger SMTP accounts.'
                ];
            }
        }

        $saveData = [
            'name'         => trim($data['name'] ?? ''),
            'sender_email' => trim($data['sender_email'] ?? ''),
            'sender_name'  => trim($data['sender_name'] ?? ''),
            'smtp_host'    => trim($data['smtp_host'] ?? 'smtp.hostinger.com') ?: 'smtp.hostinger.com',
            'smtp_port'    => (int)($data['smtp_port'] ?? 465) ?: 465,
            'smtp_crypto'  => strtolower(trim($data['smtp_crypto'] ?? 'ssl')) ?: 'ssl',
            'smtp_user'    => trim($data['smtp_user'] ?? ''),
            'daily_limit'  => (int)($data['daily_limit'] ?? 100) ?: 100,
            'status'       => in_array($data['status'] ?? '', ['active', 'disabled']) ? $data['status'] : 'active',
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        // Only update password if provided
        if (!empty($data['smtp_pass'])) {
            $saveData['smtp_pass'] = trim($data['smtp_pass']);
        }

        if ($id) {
            $this->db->where('id', $id)->update($this->table, $saveData);
            return ['success' => true, 'id' => $id, 'message' => 'Hostinger SMTP account updated successfully.'];
        } else {
            $saveData['sent_today']      = 0;
            $saveData['last_reset_date'] = date('Y-m-d');
            $saveData['is_deleted']      = 0;
            $saveData['created_at']       = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $saveData);
            $newId = $this->db->insert_id();
            return ['success' => true, 'id' => $newId, 'message' => 'Hostinger SMTP account added successfully to pool.'];
        }
    }

    /**
     * Soft delete an SMTP account
     */
    public function delete_account($id) {
        return $this->db->where('id', (int)$id)->update($this->table, [
            'is_deleted' => 1,
            'status'     => 'disabled',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Reset today's sent counter manually for an account
     */
    public function reset_sent_today($id) {
        return $this->db->where('id', (int)$id)->update($this->table, [
            'sent_today'      => 0,
            'last_reset_date' => date('Y-m-d'),
            'status'          => 'active',
            'last_error'      => null,
            'updated_at'      => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Test SMTP connection (Hostinger SSL port 465 or TLS port 587)
     */
    public function test_connection($account) {
        $host   = trim($account['smtp_host'] ?: 'smtp.hostinger.com');
        $port   = (int)($account['smtp_port'] ?: 465);
        $crypto = strtolower(trim($account['smtp_crypto'] ?: 'ssl'));

        $prefix = ($crypto === 'ssl' && $port === 465) ? 'ssl://' : '';
        $target = $prefix . $host;

        $timeout = 8;
        $errno = 0;
        $errstr = '';

        $startTime = microtime(true);
        $fp = @fsockopen($target, $port, $errno, $errstr, $timeout);
        $latency = round((microtime(true) - $startTime) * 1000);

        if (!$fp) {
            return [
                'success' => false,
                'message' => "Connection to {$host}:{$port} failed: {$errstr} (Error #{$errno}). Check firewall or Hostinger server settings."
            ];
        }

        stream_set_timeout($fp, 5);
        $greeting = fgets($fp, 512);
        fclose($fp);

        if ($greeting && (strpos($greeting, '220') !== false || strpos($greeting, 'ESMTP') !== false)) {
            return [
                'success' => true,
                'latency' => $latency,
                'message' => "Hostinger SMTP Connection Successful! Response ({$latency}ms): " . trim($greeting)
            ];
        } else {
            return [
                'success' => false,
                'message' => "Connected to {$host}:{$port}, but server returned: " . trim($greeting ?: 'No response')
            ];
        }
    }
}
