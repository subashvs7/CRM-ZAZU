<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Leads extends MY_Controller {

    private $normalized_map = [
        'firstname'           => 'first_name',
        'lastname'            => 'last_name',
        'title'               => 'title',
        'companyname'         => 'company_name',
        'company'             => 'company_name',
        'email'               => 'email',
        'emailstatus'         => 'email_status',
        'secondaryemail'      => 'secondary_email',
        'corporatephone'      => 'corporate_phone',
        'accountowner'        => 'account_owner',
        'employees'           => 'employees_count',
        'employeescount'      => 'employees_count',
        'noofemployees'       => 'employees_count',
        'numberofemployees'   => 'employees_count',
        'industry'            => 'industry',
        'keywords'            => 'keywords',
        'personlinkedinurl'   => 'person_linkedin_url',
        'personlinkedin'      => 'person_linkedin_url',
        'website'             => 'website',
        'companylinkedinurl'  => 'company_linkedin_url',
        'companylinkedin'     => 'company_linkedin_url',
        'facebookurl'         => 'facebook_url',
        'facebook'            => 'facebook_url',
        'twitterurl'          => 'twitter_url',
        'twitter'             => 'twitter_url',
        'address'             => 'address',
        'city'                => 'city',
        'state'               => 'state',
        'country'             => 'country',
        'companyaddress'      => 'company_address',
        'companycity'         => 'company_city',
        'companystate'        => 'company_state',
        'companycountry'      => 'company_country',
        'companyphone'        => 'company_phone',
        'technologies'        => 'technologies',
        'annualrevenue'       => 'annual_revenue',
        'revenue'             => 'annual_revenue',
        'emailsent'           => 'email_sent',
        'emailopen'           => 'email_open',
        'emailopened'         => 'email_open',
        'emailbounced'        => 'email_bounced',
        'demo'                => 'product_demo',
        'demostatus'          => 'product_demo',
        'demodone'            => 'product_demo',
        'demoproduct'         => 'product_demo',
        'demodetails'         => 'product_demo',
        'productdemo'         => 'product_demo',
        'demorequested'       => 'product_demo',
        'product'             => 'product_name',
        'productname'         => 'product_name',
        'interestedproduct'   => 'product_name',
        'campaign'            => 'keywords',
        'quotation'           => 'quotation',
        'quote'               => 'quotation',
    ];

    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Lead_model','Lead_activity_model','Customer_model','User_model','Product_model']);
    }

    public function index() {
        $customers     = $this->Customer_model->get_active($this->is_manager() ? [] : ['assigned_to' => $this->get_user_id()]);
        $staff         = $this->is_manager() ? $this->User_model->get_field_staff() : [];
        $products      = $this->Product_model->get_active_with_category();
        $lists_summary = $this->Lead_model->get_product_lists_summary();

        $this->load_view('leads/index', [
            'page_title'    => 'Ads Leads & Lists',
            'page_js'       => 'leads',
            'customers'     => $customers,
            'staff'         => $staff,
            'products'      => $products,
            'lists_summary' => $lists_summary,
            'sf'            => ''
        ]);
    }

    /**
     * AJAX: Get live Apollo style Product / Campaign Lists summary
     */
    public function get_product_lists_ajax() {
        $summary = $this->Lead_model->get_product_lists_summary();
        $this->json_success($summary);
    }

    /**
     * AJAX: Create a new Product / Campaign List (Apollo.io style)
     */
    public function create_product_list_ajax() {
        $name = trim($this->input->post('name'));
        $sku  = trim($this->input->post('sku'));
        $desc = trim($this->input->post('description'));

        if (!$name) {
            $this->json_error('List / Product Name is required.');
        }

        if (!$sku) {
            $sku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10));
        }

        $existing = $this->db->get_where('products', ['name' => $name, 'is_deleted' => 0])->row_array();
        if ($existing) {
            $this->json_error('A product / list with this name already exists.');
        }

        $id = $this->Product_model->insert([
            'name'        => $name,
            'sku'         => $sku,
            'description' => $desc,
            'price'       => 0,
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        $this->json_success([
            'id'   => $id,
            'name' => $name,
            'sku'  => $sku
        ], 'List "' . esc_html($name) . '" created successfully.');
    }

    /**
     * AJAX: Delete/Archive a Product / Campaign List
     */
    public function delete_product_list_ajax() {
        $id = (int)$this->input->post('id');
        if (!$id) {
            $this->json_error('Invalid list ID.');
        }

        // Soft delete the product
        $this->Product_model->delete($id);

        // Reset product_id on associated leads to NULL
        $this->db->where('product_id', $id)->update('crm_leads', ['product_id' => null]);

        $this->json_success([], 'List deleted and leads unassigned successfully.');
    }

    public function pipeline() {
        redirect('leads');
    }

    public function datatable() {
        $params = $this->input->get();
        $sf     = $this->input->get('status_filter');
        [$rows, $total] = $this->Lead_model->datatable($params, $sf, $this->get_user_id(), $this->get_role());
        $data = [];
        foreach ($rows as $r) {
            $actions = '<div class="flex items-center justify-end gap-1.5">';
            $actions .= '<button type="button" class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 hover:bg-cyan-100 flex items-center justify-center transition-colors btn-view-lead" data-id="'.$r['id'].'" title="View All Details"><i class="fa fa-eye text-xs"></i></button>';
            $actions .= '<a href="'.base_url('leads/detail/'.$r['id']).'" class="w-7 h-7 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors" title="Timeline & Activities"><i class="fa fa-history text-xs"></i></a>';
            if ($r['status']!=='deleted') {
                $actions .= '<button type="button" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors btn-edit-lead" data-id="'.$r['id'].'" title="Edit Lead"><i class="fa fa-pencil text-xs"></i></button>';
            }
            if ($r['status']==='active') {
                $actions .= '<button type="button" class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors btn-lead-status" data-id="'.$r['id'].'" data-action="deactivate" title="Deactivate"><i class="fa fa-ban text-xs"></i></button>';
                $actions .= '<button type="button" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors btn-lead-status" data-id="'.$r['id'].'" data-action="delete" title="Delete"><i class="fa fa-trash text-xs"></i></button>';
            }
            if ($r['status']==='inactive') {
                $actions .= '<button type="button" class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors btn-lead-status" data-id="'.$r['id'].'" data-action="activate" title="Activate"><i class="fa fa-check text-xs"></i></button>';
            }
            if ($r['status']==='deleted') {
                $actions .= '<button type="button" class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors btn-lead-status" data-id="'.$r['id'].'" data-action="restore" title="Restore"><i class="fa fa-undo text-xs"></i></button>';
            }
            $actions .= '</div>';

            // Checkbox
            $checkboxHtml = '<div class="flex items-center justify-center"><input type="checkbox" class="lead-row-checkbox w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer" value="'.$r['id'].'"></div>';

            // Contact Name & Title
            $fullName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $displayName = $fullName ?: ($r['title'] ?: 'Lead #'.$r['id']);
            $initial = strtoupper(substr($displayName, 0, 1));
            $nameHtml = '<div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center flex-shrink-0">'.$initial.'</div>
                <div class="min-w-0">
                    <button type="button" class="text-sm font-semibold text-gray-800 hover:text-blue-600 text-left truncate block btn-view-lead" data-id="'.$r['id'].'">'.esc_html($displayName).'</button>
                    <span class="text-xs text-gray-400 block truncate">'.esc_html($r['title']).'</span>
                </div>
            </div>';

            // Company & Industry
            $companyName = $r['company_name'] ?: ($r['customer_name'] ?: '-');
            $industryMeta = [];
            if (!empty($r['industry'])) $industryMeta[] = esc_html($r['industry']);
            if (!empty($r['employees_count'])) $industryMeta[] = esc_html($r['employees_count']) . ' emp';
            $companyHtml = '<div>
                <div class="text-sm font-semibold text-gray-800">'.esc_html($companyName).'</div>
                <div class="text-xs text-gray-400">'.(!empty($industryMeta) ? implode(' • ', $industryMeta) : '-').'</div>
            </div>';

            // Email & Status
            $emailHtml = '-';
            if (!empty($r['email'])) {
                $statusBadge = email_status_badge($r['email_status'] ?? '');
                $emailHtml = '<div>
                    <a href="mailto:'.esc_html($r['email']).'" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-mono">
                        <i class="fa fa-envelope-o text-[10px]"></i> '.esc_html($r['email']).'
                    </a>
                    '.($statusBadge ? '<div class="mt-1">'.$statusBadge.'</div>' : '').'
                </div>';
            }

            // Phone
            $phone = $r['corporate_phone'] ?: ($r['customer_phone'] ?: '');
            $phoneHtml = $phone ? '<a href="tel:'.esc_html($phone).'" class="text-xs text-gray-700 hover:text-blue-600 flex items-center gap-1 font-mono"><i class="fa fa-phone text-emerald-600 text-[10px]"></i> '.esc_html($phone).'</a>' : '<span class="text-gray-400 text-xs">-</span>';

            // Account Owner
            $owner = $r['account_owner'] ?: ($r['assigned_name'] ?: '-');
            $ownerHtml = '<span class="text-xs font-medium text-gray-700">'.esc_html($owner).'</span>';

            // Location
            $loc = array_filter([$r['city'] ?? '', $r['state'] ?? '', $r['country'] ?? '']);
            $locHtml = !empty($loc) ? '<span class="text-xs text-gray-700 flex items-center gap-1"><i class="fa fa-map-marker text-rose-500 text-[11px]"></i> '.esc_html(implode(', ', $loc)).'</span>' : '<span class="text-gray-400 text-xs">-</span>';

            // Email Tracking (Sent, Open, Bounced)
            $tracking = [];
            if (!empty($r['email_sent'])) {
                $isSent = in_array(strtolower($r['email_sent']), ['yes', 'true', '1', 'sent']);
                $tracking[] = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold '.($isSent ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-600').'">Sent: '.esc_html($r['email_sent']).'</span>';
            }
            if (!empty($r['email_open'])) {
                $isOpen = in_array(strtolower($r['email_open']), ['yes', 'true', '1', 'opened']);
                $tracking[] = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold '.($isOpen ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600').'">Open: '.esc_html($r['email_open']).'</span>';
            }
            if (!empty($r['email_bounced'])) {
                $isBounced = in_array(strtolower($r['email_bounced']), ['yes', 'true', '1', 'bounced']);
                $tracking[] = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold '.($isBounced ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200').'">Bounce: '.esc_html($r['email_bounced']).'</span>';
            }
            $outreachHtml = !empty($tracking) ? '<div class="flex flex-col gap-1 items-start">'.implode('', $tracking).'</div>' : '<span class="text-gray-400 text-xs">-</span>';

            // Demo & Quote & Linked Product
            $demoQuote = [];
            if (!empty($r['product_name'])) {
                $demoQuote[] = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1" title="Linked Product"><i class="fa fa-cube text-emerald-600 text-[9px]"></i> '.esc_html($r['product_name']).'</span>';
            }
            $demoVal = !empty($r['product_demo']) ? $r['product_demo'] : ($r['demo'] ?? '');
            if (!empty($demoVal)) {
                $demoQuote[] = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200"><i class="fa fa-desktop mr-1 text-[9px]"></i>Demo: '.esc_html($demoVal).'</span>';
            }
            if (!empty($r['quotation'])) {
                $demoQuote[] = '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"><i class="fa fa-file-text-o mr-1 text-[9px]"></i>Quote: '.esc_html($r['quotation']).'</span>';
            }
            $demoQuoteHtml = !empty($demoQuote) ? '<div class="flex flex-col gap-1 items-start">'.implode('', $demoQuote).'</div>' : '<span class="text-gray-400 text-xs">-</span>';

            // Stage & Status
            $statusHtml = '<div class="flex flex-col gap-1 items-start">'.lead_status_badge($r['lead_status']).status_badge($r['status']).'</div>';

            $data[] = [
                $checkboxHtml,
                $r['id'],
                $nameHtml,
                $companyHtml,
                $emailHtml,
                $phoneHtml,
                $ownerHtml,
                $locHtml,
                $outreachHtml,
                $demoQuoteHtml,
                $statusHtml,
                date('d M Y', strtotime($r['created_at'])),
                $actions,
            ];
        }
        $this->json_list($data, $total, $total);
    }

    public function save() {
        $id                  = (int) $this->input->post('id');
        $first_name          = trim($this->input->post('first_name') ?? '');
        $last_name           = trim($this->input->post('last_name') ?? '');
        $title               = trim($this->input->post('title') ?? '');
        $company_name        = trim($this->input->post('company_name') ?? '');
        $customer_id         = (int) $this->input->post('customer_id') ?: null;
        $email               = trim($this->input->post('email') ?? '');
        $email_status        = trim($this->input->post('email_status') ?? '');
        $secondary_email     = trim($this->input->post('secondary_email') ?? '');
        $corporate_phone     = trim($this->input->post('corporate_phone') ?? '');
        $account_owner       = trim($this->input->post('account_owner') ?? '');
        $employees_count     = trim($this->input->post('employees_count') ?? '');
        $industry            = trim($this->input->post('industry') ?? '');
        $keywords            = trim($this->input->post('keywords') ?? '');
        $person_linkedin_url = trim($this->input->post('person_linkedin_url') ?? '');
        $website             = trim($this->input->post('website') ?? '');
        $company_linkedin_url= trim($this->input->post('company_linkedin_url') ?? '');
        $facebook_url        = trim($this->input->post('facebook_url') ?? '');
        $twitter_url         = trim($this->input->post('twitter_url') ?? '');
        $address             = trim($this->input->post('address') ?? '');
        $city                = trim($this->input->post('city') ?? '');
        $state               = trim($this->input->post('state') ?? '');
        $country             = trim($this->input->post('country') ?? '');
        $company_address     = trim($this->input->post('company_address') ?? '');
        $company_city        = trim($this->input->post('company_city') ?? '');
        $company_state       = trim($this->input->post('company_state') ?? '');
        $company_country     = trim($this->input->post('company_country') ?? '');
        $company_phone       = trim($this->input->post('company_phone') ?? '');
        $technologies        = trim($this->input->post('technologies') ?? '');
        $annual_revenue      = trim($this->input->post('annual_revenue') ?? '');
        $email_sent          = trim($this->input->post('email_sent') ?? '');
        $email_open          = trim($this->input->post('email_open') ?? '');
        $email_bounced       = trim($this->input->post('email_bounced') ?? '');
        $product_demo        = trim($this->input->post('product_demo') ?? $this->input->post('demo') ?? '');
        $quotation           = trim($this->input->post('quotation') ?? '');

        // Auto-generate title if blank
        if (!$title) {
            $name = trim($first_name . ' ' . $last_name);
            if ($name) $title = $name . ($company_name ? ' (' . $company_name . ')' : '');
            elseif ($company_name) $title = $company_name;
            else $title = 'Lead ' . date('Ymd-His');
        }

        // Email validation
        $errors = [];
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($secondary_email && !filter_var($secondary_email, FILTER_VALIDATE_EMAIL)) {
            $errors['secondary_email'] = 'Please enter a valid secondary email address.';
        }
        if ($errors) {
            $this->json_error('Validation failed.', 400, $errors);
        }

        // If customer_id is empty but company_name matches a customer, auto-link
        if (!$customer_id && $company_name) {
            $cust = $this->db->get_where('crm_customers', ['customer_org_name' => $company_name, 'is_deleted' => 0])->row_array();
            if ($cust) {
                $customer_id = (int)$cust['id'];
            }
        }

        // Auto-match product_id from product_demo if product_id is not manually selected
        $product_id = (int)$this->input->post('product_id') ?: null;
        if (!$product_id && $product_demo) {
            $active_products = $this->Product_model->get_active_with_category();
            $matchedP = $this->_match_product_from_keywords(['product_demo' => $product_demo], $active_products);
            if ($matchedP) {
                $product_id = (int)$matchedP['id'];
            }
        }

        $data = [
            'first_name'           => $first_name ?: null,
            'last_name'            => $last_name ?: null,
            'title'                => $title,
            'company_name'         => $company_name ?: null,
            'customer_id'          => $customer_id,
            'email'                => $email ?: null,
            'email_status'         => $email_status ?: null,
            'secondary_email'      => $secondary_email ?: null,
            'corporate_phone'      => $corporate_phone ?: null,
            'account_owner'        => $account_owner ?: null,
            'employees_count'      => $employees_count ?: null,
            'industry'             => $industry ?: null,
            'keywords'             => $keywords ?: null,
            'person_linkedin_url'  => $person_linkedin_url ?: null,
            'website'              => $website ?: null,
            'company_linkedin_url' => $company_linkedin_url ?: null,
            'facebook_url'         => $facebook_url ?: null,
            'twitter_url'          => $twitter_url ?: null,
            'address'              => $address ?: null,
            'city'                 => $city ?: null,
            'state'                => $state ?: null,
            'country'              => $country ?: null,
            'company_address'      => $company_address ?: null,
            'company_city'         => $company_city ?: null,
            'company_state'        => $company_state ?: null,
            'company_country'      => $company_country ?: null,
            'company_phone'        => $company_phone ?: null,
            'technologies'         => $technologies ?: null,
            'annual_revenue'       => $annual_revenue ?: null,
            'email_sent'           => $email_sent ?: null,
            'email_open'           => $email_open ?: null,
            'email_bounced'        => $email_bounced ?: null,
            'product_demo'         => $product_demo ?: null,
            'quotation'            => $quotation ?: null,
            'description'          => $this->input->post('description') ?: null,
            'source'               => $this->input->post('source') ?: 'field',
            'lead_status'          => $this->input->post('lead_status') ?: 'new',
            'assigned_to'          => (int)$this->input->post('assigned_to') ?: $this->get_user_id(),
            'product_id'           => $product_id,
            'expected_value'       => ($this->input->post('expected_value') !== null && $this->input->post('expected_value') !== '') ? inr_to_paise((float)$this->input->post('expected_value')) : null,
            'expected_close_date'  => $this->input->post('expected_close_date') ?: null,
        ];

        if ($id) {
            $this->Lead_model->update($id, $data);
            $this->json_success([], 'Lead updated successfully.');
        } else {
            $new = $this->Lead_model->insert($data);
            $this->json_success(['id'=>$new], 'Lead created successfully.');
        }
    }

    public function get($id) {
        $lead = $this->Lead_model->get_with_details($id);
        if (!$lead) $this->json_error('Lead not found.', 404);
        $this->json_success($lead);
    }

    public function detail($id) {
        $lead = $this->Lead_model->get_with_details($id);
        if (!$lead) show_404();
        $activities = $this->Lead_activity_model->get_by_lead($id);
        $this->load_view('leads/_detail', ['page_title'=>$lead['title'],'lead'=>$lead,'activities'=>$activities]);
    }

    public function update_status() {
        $ids = $this->input->post('ids');
        if (!empty($ids) && is_array($ids)) {
            return $this->bulk_status();
        }

        $id = (int)$this->input->post('id');
        $action = $this->input->post('action');
        switch($action){
            case 'activate':   $this->Lead_model->activate($id); break;
            case 'deactivate': $this->Lead_model->deactivate($id); break;
            case 'delete':     $this->Lead_model->soft_delete($id); break;
            case 'restore':    $this->Lead_model->restore($id); break;
            case 'permanent_delete':
                if (!$this->is_manager()) $this->json_error('Only managers/admins can permanently delete.');
                $this->db->where('lead_id', $id)->delete('crm_lead_activities');
                $this->db->where('lead_id', $id)->delete('crm_bulk_mail_queue');
                $this->db->where('id', $id)->delete('crm_leads');
                break;
            default: $this->json_error('Invalid status action.');
        }
        $this->json_success([], 'Lead status updated.');
    }

    /**
     * AJAX: Multi-Select Bulk Actions (Delete, Restore, Permanent Delete)
     */
    public function bulk_status() {
        $ids = $this->input->post('ids');
        $action = $this->input->post('action');

        if (empty($ids) || !is_array($ids)) {
            $this->json_error('Please select at least one lead.');
        }

        $clean_ids = array_filter(array_map('intval', $ids));
        if (empty($clean_ids)) {
            $this->json_error('Invalid lead IDs.');
        }

        $count = count($clean_ids);
        switch($action) {
            case 'delete':
                $this->db->where_in('id', $clean_ids)->update('crm_leads', [
                    'status'     => 'deleted',
                    'is_deleted' => 1,
                    'deleted_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $msg = "{$count} lead(s) moved to deleted.";
                break;

            case 'restore':
                $this->db->where_in('id', $clean_ids)->update('crm_leads', [
                    'status'     => 'active',
                    'is_deleted' => 0,
                    'deleted_at' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $msg = "{$count} lead(s) restored successfully.";
                break;

            case 'permanent_delete':
                if (!$this->is_manager()) {
                    $this->json_error('Only managers/admins can permanently delete leads.');
                }
                $this->db->where_in('lead_id', $clean_ids)->delete('crm_lead_activities');
                $this->db->where_in('lead_id', $clean_ids)->delete('crm_bulk_mail_queue');
                $this->db->where_in('id', $clean_ids)->delete('crm_leads');
                $msg = "{$count} lead(s) permanently deleted.";
                break;

            case 'activate':
                $this->db->where_in('id', $clean_ids)->update('crm_leads', [
                    'status'     => 'active',
                    'is_deleted' => 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $msg = "{$count} lead(s) activated.";
                break;

            case 'deactivate':
                $this->db->where_in('id', $clean_ids)->update('crm_leads', [
                    'status'     => 'inactive',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $msg = "{$count} lead(s) deactivated.";
                break;

            default:
                $this->json_error('Invalid bulk action.');
        }

        $this->json_success(['count' => $count], $msg);
    }

    public function add_activity() {
        $lead_id = (int)$this->input->post('lead_id');
        if (!$lead_id) $this->json_error('Lead ID required.');
        $type = $this->input->post('activity_type');
        $notes = $this->input->post('notes');
        $stage = $this->input->post('stage');

        $data = [
            'lead_id'       => $lead_id,
            'user_id'       => $this->get_user_id(),
            'activity_type' => $type,
            'notes'         => $notes,
            'occurred_at'   => date('Y-m-d H:i:s'),
        ];
        $this->Lead_activity_model->insert($data);

        if ($stage) $this->Lead_model->update($lead_id, ['lead_status' => $stage]);
        $this->json_success([], 'Activity logged successfully.');
    }

    public function activities($lead_id) {
        $acts = $this->Lead_activity_model->get_by_lead($lead_id);
        $this->json_success($acts);
    }

    public function import() {
        redirect('leads?open_import=1');
    }

    public function sample_template() {
        $format = strtolower($this->input->get('format') ?: 'xlsx');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leads Template');

        $headers = [
            'First Name', 'Last Name', 'Title', 'Company Name', 'Email', 'Email Status',
            'Secondary Email', 'Corporate Phone', 'Account Owner', '# Employees', 'Industry',
            'Keywords', 'Person Linkedin Url', 'Website', 'Company Linkedin Url', 'Facebook Url',
            'Twitter Url', 'Address', 'City', 'State', 'Country', 'Company Address', 'Company City',
            'Company State', 'Company Country', 'Company Phone', 'Technologies', 'Annual Revenue',
            'Email Sent', 'Email Open', 'Email Bounced', 'Demo', 'Quotation'
        ];

        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx, 1, $h);
            $colIdx++;
        }

        $sample1 = [
            'Rajesh', 'Kumar', 'CTO', 'TechNova Solutions', 'rajesh@technova.com', 'Valid',
            'rajesh.personal@gmail.com', '+91 9876543210', 'admin', '50-100', 'Information Technology',
            'Cloud, SaaS, AI', 'https://linkedin.com/in/rajeshkumar', 'https://technova.com',
            'https://linkedin.com/company/technova', 'https://facebook.com/technova', 'https://twitter.com/technova',
            '123 Anna Salai', 'Chennai', 'Tamil Nadu', 'India', '456 OMR IT Corridor', 'Chennai',
            'Tamil Nadu', 'India', '+91 44 28765432', 'PHP, React, AWS, MySQL', '$2M - $5M',
            'Yes', 'Yes', 'No', 'Completed', 'Sent'
        ];

        $sample2 = [
            'Ananya', 'Sharma', 'VP Marketing', 'Apex Global Ventures', 'ananya.s@apexglobal.com', 'Verified',
            'ananya.sharma@yahoo.com', '+91 9123456789', 'admin', '100-250', 'Digital Marketing',
            'B2B, Lead Gen, CRM', 'https://linkedin.com/in/ananyasharma', 'https://apexglobal.com',
            'https://linkedin.com/company/apexglobal', 'https://facebook.com/apexglobal', 'https://twitter.com/apexglobal',
            '78 MG Road', 'Bengaluru', 'Karnataka', 'India', 'Whitefield Tech Park', 'Bengaluru',
            'Karnataka', 'India', '+91 80 41234567', 'HubSpot, WordPress, Node.js', '$5M - $10M',
            'Yes', 'No', 'No', 'Scheduled', 'Draft'
        ];

        $colIdx = 1;
        foreach ($sample1 as $val) {
            $sheet->setCellValueByColumnAndRow($colIdx, 2, $val);
            $colIdx++;
        }
        $colIdx = 1;
        foreach ($sample2 as $val) {
            $sheet->setCellValueByColumnAndRow($colIdx, 3, $val);
            $colIdx++;
        }

        // Auto-fit column widths
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:AG1')->getFont()->setBold(true);

        if (ob_get_level()) ob_clean();
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="leads_import_template.csv"');
            header('Cache-Control: max-age=0');
            $writer = new Csv($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="leads_import_template.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }

    public function export() {
        $format = strtolower($this->input->get('format') ?: 'xlsx');
        $status_filter = $this->input->get('status_filter') ?: 'active';
        $product_id = $this->input->get('product_id');
        [$rows, $total] = $this->Lead_model->datatable(['length' => -1, 'product_id' => $product_id], $status_filter, $this->get_user_id(), $this->get_role());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leads Export');

        $headers = [
            'First Name', 'Last Name', 'Title', 'Company Name', 'Email', 'Email Status',
            'Secondary Email', 'Corporate Phone', 'Account Owner', '# Employees', 'Industry',
            'Keywords', 'Person Linkedin Url', 'Website', 'Company Linkedin Url', 'Facebook Url',
            'Twitter Url', 'Address', 'City', 'State', 'Country', 'Company Address', 'Company City',
            'Company State', 'Company Country', 'Company Phone', 'Technologies', 'Annual Revenue',
            'Email Sent', 'Email Open', 'Email Bounced', 'Demo', 'Quotation'
        ];

        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx, 1, $h);
            $colIdx++;
        }

        $rowIdx = 2;
        foreach ($rows as $r) {
            $rowData = [
                $r['first_name'] ?? '',
                $r['last_name'] ?? '',
                $r['title'] ?? '',
                $r['company_name'] ?: ($r['customer_name'] ?? ''),
                $r['email'] ?? '',
                $r['email_status'] ?? '',
                $r['secondary_email'] ?? '',
                $r['corporate_phone'] ?? '',
                $r['account_owner'] ?: ($r['assigned_name'] ?? ''),
                $r['employees_count'] ?? '',
                $r['industry'] ?? '',
                $r['keywords'] ?? '',
                $r['person_linkedin_url'] ?? '',
                $r['website'] ?? '',
                $r['company_linkedin_url'] ?? '',
                $r['facebook_url'] ?? '',
                $r['twitter_url'] ?? '',
                $r['address'] ?? '',
                $r['city'] ?? '',
                $r['state'] ?? '',
                $r['country'] ?? '',
                $r['company_address'] ?? '',
                $r['company_city'] ?? '',
                $r['company_state'] ?? '',
                $r['company_country'] ?? '',
                $r['company_phone'] ?? '',
                $r['technologies'] ?? '',
                $r['annual_revenue'] ?? '',
                $r['email_sent'] ?? '',
                $r['email_open'] ?? '',
                $r['email_bounced'] ?? '',
                $r['product_demo'] ?? ($r['demo'] ?? ''),
                $r['quotation'] ?? '',
            ];

            $colIdx = 1;
            foreach ($rowData as $val) {
                $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $val);
                $colIdx++;
            }
            $rowIdx++;
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:AG1')->getFont()->setBold(true);

        $filename = 'leads_export_' . date('Ymd_His');
        if (ob_get_level()) ob_clean();
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
            header('Cache-Control: max-age=0');
            $writer = new Csv($spreadsheet);
            $writer->save('php://output');
            exit;
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }

    private function _get_phone_variants($phone) {
        if (!$phone) return [];
        $raw = trim((string)$phone);
        if ($raw === '') return [];
        $digits = preg_replace('/\D+/', '', $raw);
        $variants = [];
        if ($digits !== '') {
            $variants[] = $digits;
            if (strlen($digits) >= 10) {
                $variants[] = substr($digits, -10);
            }
        }
        $variants[] = strtolower(preg_replace('/\s+/', '', $raw));
        return array_unique($variants);
    }

    /**
     * Dynamically match spreadsheet row data (Demo column, product name, keywords, technologies, industry, company)
     * against active system products.
     * Special high priority is given to the 'Demo' column as requested.
     */
    private function _match_product_from_keywords($leadData, $products) {
        if (empty($products)) return null;

        $demoText  = strtolower(trim($leadData['product_demo'] ?? $leadData['demo'] ?? ''));
        $prodText  = strtolower(trim($leadData['product_name'] ?? ''));
        $kwText    = strtolower(trim($leadData['keywords'] ?? ''));
        $techText  = strtolower(trim($leadData['technologies'] ?? ''));
        $indText   = strtolower(trim($leadData['industry'] ?? ''));
        $compText  = strtolower(trim($leadData['company_name'] ?? ''));
        $titleText = strtolower(trim($leadData['title'] ?? ''));

        $combinedText = trim($demoText . ' ' . $prodText . ' ' . $kwText . ' ' . $techText . ' ' . $indText . ' ' . $compText . ' ' . $titleText);
        if ($combinedText === '') return null;

        $cleanDemo = preg_replace('/[^a-z0-9]/', '', $demoText);
        $cleanProd = preg_replace('/[^a-z0-9]/', '', $prodText);

        $bestProduct = null;
        $highestScore = 0;

        foreach ($products as $p) {
            $score = 0;
            $pName = strtolower(trim($p['name'] ?? ''));
            $pSku  = strtolower(trim($p['sku'] ?? ''));
            $pCat  = strtolower(trim($p['category_name'] ?? ''));
            $pDesc = strtolower(trim($p['description'] ?? ''));

            $cleanPName = preg_replace('/[^a-z0-9]/', '', $pName);
            $cleanSku   = preg_replace('/[^a-z0-9]/', '', $pSku);

            // --- 1. DIRECT PRODUCT_DEMO COLUMN MATCHING (ABSOLUTE HIGHEST PRIORITY) ---
            if ($cleanDemo !== '') {
                // Exact match with SKU or full Product Name
                if ($cleanDemo === $cleanSku || $cleanDemo === $cleanPName) {
                    $score += 300;
                }
                // Exact / Substring match against SKU (e.g. "classwall", "promancm")
                elseif ($cleanSku !== '' && (strpos($cleanDemo, $cleanSku) !== false || strpos($cleanSku, $cleanDemo) !== false)) {
                    $score += 250;
                }
                // Direct match against full normalized product name
                elseif ($cleanPName !== '' && (strpos($cleanDemo, $cleanPName) !== false || strpos($cleanPName, $cleanDemo) !== false)) {
                    $score += 220;
                }
                // Raw text match
                if ($pSku !== '' && stripos($demoText, $pSku) !== false) {
                    $score += 180;
                }
                if ($pName !== '' && stripos($demoText, $pName) !== false) {
                    $score += 180;
                }

                // Word-level match in demo column
                $nameWords = preg_split('/[\s\-_,\.\(\)\/]+/', $pName, -1, PREG_SPLIT_NO_EMPTY);
                $primaryWordMatchCount = 0;
                foreach ($nameWords as $w) {
                    if (strlen($w) >= 3 && !in_array($w, ['erp', 'crm', 'app', 'the', 'and', 'for', 'ltd', 'inc', 'pvt', 'hub', 'management', 'software', 'system'])) {
                        if (stripos($demoText, $w) !== false) {
                            $score += 60;
                            $primaryWordMatchCount++;
                        }
                    }
                }
                if ($primaryWordMatchCount >= 2) {
                    $score += 60;
                }

                // Check SKU parts in demo (e.g. "PROMAN-CM" -> "proman")
                $skuWords = preg_split('/[\s\-_]+/', $pSku, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($skuWords as $sw) {
                    if (strlen($sw) >= 3 && stripos($demoText, $sw) !== false) {
                        $score += 70;
                    }
                }
            }

            // --- 2. DEDICATED PRODUCT NAME COLUMN (IF PRESENT IN SHEET) ---
            if ($cleanProd !== '') {
                if ($cleanSku !== '' && (strpos($cleanProd, $cleanSku) !== false || strpos($cleanSku, $cleanProd) !== false)) {
                    $score += 150;
                }
                if ($cleanPName !== '' && (strpos($cleanProd, $cleanPName) !== false || strpos($cleanPName, $cleanProd) !== false)) {
                    $score += 140;
                }
            }

            // --- 3. KEYWORDS, TECH, INDUSTRY, COMPANY & TITLE MATCHING ---
            if ($pName !== '' && stripos($combinedText, $pName) !== false) {
                $score += 50;
            }
            if ($pSku !== '' && stripos($combinedText, $pSku) !== false) {
                $score += 45;
            }

            $nameWords = preg_split('/[\s\-_,\.\(\)\/]+/', $pName, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($nameWords as $w) {
                if (strlen($w) > 2 && !in_array($w, ['erp', 'crm', 'app', 'the', 'and', 'for', 'ltd', 'inc', 'pvt', 'hub', 'management', 'software', 'system'])) {
                    if (stripos($combinedText, $w) !== false) {
                        $score += (stripos($kwText, $w) !== false ? 30 : 15);
                    }
                }
            }

            // Category match
            if ($pCat !== '' && stripos($combinedText, $pCat) !== false) {
                $score += 20;
            }

            if ($score > $highestScore && $score >= 20) {
                $highestScore = $score;
                $bestProduct = $p;
            }
        }

        return $bestProduct;
    }

    /**
     * Parse uploaded spreadsheet file into rows and map recognized column headers.
     * Memory and execution time limits are increased for large spreadsheets.
     */
    private function _parse_uploaded_spreadsheet() {
        if (!isset($_FILES['file']) && !isset($_FILES['csv_file'])) {
            $this->json_error('No spreadsheet file selected.');
        }

        $file = $_FILES['file'] ?? $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json_error('File upload failed with error code ' . $file['error']);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            $this->json_error('Invalid file type (.'.$ext.'). Please upload an Excel (.xlsx/.xls) or CSV file.');
        }

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            $this->json_error('Failed to parse spreadsheet: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            $this->json_error('Uploaded sheet contains no data rows.');
        }

        $headers = $rows[0];
        $header_map = [];
        foreach ($headers as $colIdx => $h) {
            if ($h === null || trim((string)$h) === '') continue;
            $norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$h));
            if (isset($this->normalized_map[$norm])) {
                $header_map[$colIdx] = $this->normalized_map[$norm];
            }
        }

        if (empty($header_map)) {
            $this->json_error('Could not identify recognized column headers. Please download the sample template.');
        }

        return [$file, $rows, $header_map];
    }

    /**
     * Preload all active CRM leads indexed by valid Email and Secondary Email.
     * Duplicate checking is strictly by Email (same mobile with different email is allowed as separate lead).
     */
    private function _get_existing_leads_by_email() {
        $existing_leads = $this->db->select('id, title, first_name, last_name, company_name, email, secondary_email, corporate_phone, company_phone')
            ->where('is_deleted', 0)
            ->get('crm_leads')
            ->result_array();

        $existing_emails = [];
        foreach ($existing_leads as $el) {
            if (!empty($el['email'])) {
                $em = strtolower(trim($el['email']));
                if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                    $existing_emails[$em] = $el;
                }
            }
            if (!empty($el['secondary_email'])) {
                $sec = strtolower(trim($el['secondary_email']));
                if (filter_var($sec, FILTER_VALIDATE_EMAIL)) {
                    $existing_emails[$sec] = $el;
                }
            }
        }

        return $existing_emails;
    }

    public function import_validate() {
        [$file, $rows, $header_map] = $this->_parse_uploaded_spreadsheet();

        // Preload active products for dynamic keyword matching
        $active_products = $this->Product_model->get_active_with_category();

        // Preload all active leads strictly indexed by Email from DB
        $existing_emails = $this->_get_existing_leads_by_email();

        // Check if user pre-selected a specific product list for this import batch
        $target_product_id = $this->input->post('target_product_id');
        $fixedProduct = null;
        if (!empty($target_product_id) && $target_product_id !== 'auto') {
            if ($target_product_id === 'new') {
                $newPName = trim($this->input->post('new_product_name'));
                if ($newPName) {
                    $newSku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $newPName), 0, 10));
                    $pId = $this->Product_model->insert([
                        'name'       => $newPName,
                        'sku'        => $newSku,
                        'status'     => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $fixedProduct = $this->Product_model->get_by_id($pId);
                }
            } else {
                $fixedProduct = $this->Product_model->get_by_id((int)$target_product_id);
            }
        }

        $new_leads              = [];
        $duplicates             = [];
        $invalid_rows           = [];
        $matched_products_count = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $row = $rows[$i];

            $hasContent = false;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string)$cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) continue;

            $leadData = [];
            foreach ($header_map as $colIdx => $field) {
                $leadData[$field] = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : '';
            }

            $first_name   = $leadData['first_name'] ?? '';
            $last_name    = $leadData['last_name'] ?? '';
            $title        = $leadData['title'] ?? '';
            $company_name = $leadData['company_name'] ?? '';
            $email        = $leadData['email'] ?? '';
            $secEmail     = $leadData['secondary_email'] ?? '';
            $corpPhone    = $leadData['corporate_phone'] ?? '';
            $compPhone    = $leadData['company_phone'] ?? '';

            // Format validation: at least 1 identifier
            if (!$first_name && !$last_name && !$title && !$company_name && !$email) {
                $invalid_rows[] = [
                    'row'     => $rowNum,
                    'field'   => 'Contact / Company',
                    'message' => 'Row missing Contact Name, Title, Company Name, or Email.'
                ];
                continue;
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid_rows[] = [
                    'row'     => $rowNum,
                    'field'   => 'Email',
                    'message' => "Invalid email format: '{$email}'."
                ];
                continue;
            }

            if ($secEmail !== '' && !filter_var($secEmail, FILTER_VALIDATE_EMAIL)) {
                $invalid_rows[] = [
                    'row'     => $rowNum,
                    'field'   => 'Secondary Email',
                    'message' => "Invalid secondary email format: '{$secEmail}'."
                ];
                continue;
            }

            // PRODUCT ASSIGNMENT: Prioritize Demo column & row keyword matching, fallback to fixed product
            $rowMatchedProduct = $this->_match_product_from_keywords($leadData, $active_products);

            if ($rowMatchedProduct) {
                $matchedProductId   = (int)$rowMatchedProduct['id'];
                $matchedProductName = $rowMatchedProduct['name'];
                $matchedProductSku  = $rowMatchedProduct['sku'];
                $leadData['product_id'] = $matchedProductId;
                $matched_products_count++;
            } elseif ($fixedProduct) {
                $matchedProductId   = (int)$fixedProduct['id'];
                $matchedProductName = $fixedProduct['name'];
                $matchedProductSku  = $fixedProduct['sku'];
                $leadData['product_id'] = $matchedProductId;
                $matched_products_count++;
            } else {
                $matchedProductId   = null;
                $matchedProductName = null;
                $matchedProductSku  = null;
            }

            // DUPLICATE VALIDATION: Strictly match against existing database records by EMAIL only
            // Same mobile number with different email ID is allowed as a valid lead.
            $matched     = null;
            $match_field = '';
            $match_val   = '';

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $normEmail = strtolower($email);
                if (isset($existing_emails[$normEmail])) {
                    $matched     = $existing_emails[$normEmail];
                    $match_field = 'Email';
                    $match_val   = $email;
                }
            }

            if (!$matched && $secEmail !== '' && filter_var($secEmail, FILTER_VALIDATE_EMAIL)) {
                $normSec = strtolower($secEmail);
                if (isset($existing_emails[$normSec])) {
                    $matched     = $existing_emails[$normSec];
                    $match_field = 'Secondary Email';
                    $match_val   = $secEmail;
                }
            }

            $displayName = trim($first_name . ' ' . $last_name) ?: ($title ?: ($company_name ?: $email));

            if ($matched) {
                $matchedName = trim(($matched['first_name'] ?? '') . ' ' . ($matched['last_name'] ?? '')) ?: ($matched['title'] ?: ($matched['company_name'] ?: 'Lead #' . $matched['id']));
                $duplicates[] = [
                    'row'                  => $rowNum,
                    'name'                 => $displayName,
                    'company'              => $company_name,
                    'email'                => $email,
                    'phone'                => $corpPhone ?: $compPhone,
                    'lead_data'            => $leadData,
                    'matched_id'           => (int)$matched['id'],
                    'matched_name'         => $matchedName,
                    'matched_company'      => $matched['company_name'] ?? '',
                    'matched_email'        => $matched['email'] ?? '',
                    'matched_phone'        => $matched['corporate_phone'] ?: ($matched['company_phone'] ?? ''),
                    'match_field'          => $match_field,
                    'match_value'          => $match_val,
                    'match_reason'         => "Matches existing Lead #{$matched['id']} ({$matchedName}) via {$match_field}: '{$match_val}'",
                    'matched_product_id'   => $matchedProductId,
                    'matched_product_name' => $matchedProductName,
                    'matched_product_sku'  => $matchedProductSku,
                ];
            } else {
                $new_leads[] = [
                    'row'                  => $rowNum,
                    'name'                 => $displayName,
                    'company'              => $company_name,
                    'email'                => $email,
                    'phone'                => $corpPhone ?: $compPhone,
                    'lead_data'            => $leadData,
                    'matched_product_id'   => $matchedProductId,
                    'matched_product_name' => $matchedProductName,
                    'matched_product_sku'  => $matchedProductSku,
                ];
            }
        }

        $totalRows = count($new_leads) + count($duplicates) + count($invalid_rows);
        $this->json_success([
            'filename'               => $file['name'],
            'total_rows'             => $totalRows,
            'new_count'              => count($new_leads),
            'duplicate_count'        => count($duplicates),
            'invalid_count'          => count($invalid_rows),
            'matched_products_count' => $matched_products_count,
            'duplicates'             => $duplicates,
            'sample_new'             => array_slice($new_leads, 0, 5),
            'invalid_rows'           => $invalid_rows,
        ], 'Spreadsheet parsed. Validation against database complete.');
    }

    public function import_confirm() {
        [$file, $rows, $header_map] = $this->_parse_uploaded_spreadsheet();

        $duplicate_action      = $this->input->post('duplicate_action') ?: 'skip'; // 'skip', 'overwrite', 'delete'
        $row_actions_raw       = $this->input->post('row_actions');
        $row_actions           = is_array($row_actions_raw) ? $row_actions_raw : (json_decode($row_actions_raw ?: '[]', true) ?: []);
        $auto_create_customers = (bool)$this->input->post('auto_create_customers');

        // Preload active products for dynamic keyword matching
        $active_products = $this->Product_model->get_active_with_category();

        // Check if user pre-selected a specific product list
        $target_product_id = $this->input->post('target_product_id');
        $fixedProduct = null;
        if (!empty($target_product_id) && $target_product_id !== 'auto') {
            if ($target_product_id === 'new') {
                $newPName = trim($this->input->post('new_product_name'));
                if ($newPName) {
                    $newSku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $newPName), 0, 10));
                    $pId = $this->Product_model->insert([
                        'name'       => $newPName,
                        'sku'        => $newSku,
                        'status'     => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $fixedProduct = $this->Product_model->get_by_id($pId);
                }
            } else {
                $fixedProduct = $this->Product_model->get_by_id((int)$target_product_id);
            }
        }

        // Preload existing leads from DB indexed strictly by email
        $existing_emails = $this->_get_existing_leads_by_email();

        // Preload users for account owner lookup
        $users = $this->db->select('id, name, email')->get('crm_user')->result_array();
        $user_lookup = [];
        foreach ($users as $u) {
            $user_lookup[strtolower(trim($u['name']))]  = $u['id'];
            $user_lookup[strtolower(trim($u['email']))] = $u['id'];
        }

        $tableFields = $this->db->list_fields('leads');

        $imported             = 0;
        $updated              = 0;
        $skipped              = 0;
        $deleted_and_replaced = 0;
        $currentUserId        = $this->get_user_id() ?: 1;

        $old_db_debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $this->db->trans_begin();

        try {
            for ($i = 1; $i < count($rows); $i++) {
                $rowNum = $i + 1;
                $row = $rows[$i];

                $hasContent = false;
                foreach ($row as $cell) {
                    if ($cell !== null && trim((string)$cell) !== '') {
                        $hasContent = true;
                        break;
                    }
                }
                if (!$hasContent) continue;

                $ld = [];
                foreach ($header_map as $colIdx => $field) {
                    $ld[$field] = isset($row[$colIdx]) ? trim((string)$row[$colIdx]) : '';
                }

                $first_name   = $ld['first_name'] ?? '';
                $last_name    = $ld['last_name'] ?? '';
                $title        = $ld['title'] ?? '';
                $company_name = $ld['company_name'] ?? '';
                $email        = $ld['email'] ?? '';
                $secEmail     = $ld['secondary_email'] ?? '';
                $corpPhone    = $ld['corporate_phone'] ?? '';
                $compPhone    = $ld['company_phone'] ?? '';

                if (!$first_name && !$last_name && !$title && !$company_name && !$email) {
                    continue; // Skip invalid row
                }

                // Match product
                $rowMatchedProduct = $this->_match_product_from_keywords($ld, $active_products);
                if ($rowMatchedProduct) {
                    $ld['product_id'] = (int)$rowMatchedProduct['id'];
                } elseif ($fixedProduct) {
                    $ld['product_id'] = (int)$fixedProduct['id'];
                }

                // Title fallback
                if (!$title) {
                    $fullName = trim($first_name . ' ' . $last_name);
                    $title = $fullName ? ($fullName . ($company_name ? ' (' . $company_name . ')' : '')) : ($company_name ?: ($email ?: 'Imported Lead'));
                }
                $ld['title'] = substr($title, 0, 200);

                // Account owner
                $assigned_to = $currentUserId;
                if (!empty($ld['account_owner'])) {
                    $ownerNorm = strtolower(trim($ld['account_owner']));
                    if (isset($user_lookup[$ownerNorm])) {
                        $assigned_to = $user_lookup[$ownerNorm];
                    }
                }
                $ld['assigned_to'] = $assigned_to;

                // Check duplicate against existing DB leads strictly by Email
                // Same mobile number with different email ID is allowed as a new lead.
                $matched = null;
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $normEmail = strtolower($email);
                    if (isset($existing_emails[$normEmail])) {
                        $matched = $existing_emails[$normEmail];
                    }
                }
                if (!$matched && $secEmail !== '' && filter_var($secEmail, FILTER_VALIDATE_EMAIL)) {
                    $normSec = strtolower($secEmail);
                    if (isset($existing_emails[$normSec])) {
                        $matched = $existing_emails[$normSec];
                    }
                }

                if ($matched) {
                    $matchedId = (int)$matched['id'];
                    $action = $row_actions[(string)$rowNum] ?? $duplicate_action;

                    if ($action === 'skip' || $action === 'no') {
                        $skipped++;
                        continue;
                    }

                    if ($action === 'overwrite' || $action === 'yes' || $action === 'update') {
                        $updateFields = [];
                        foreach ($ld as $col => $val) {
                            if (in_array($col, $tableFields) && $val !== '' && $val !== null && $col !== 'id') {
                                $updateFields[$col] = $val;
                            }
                        }
                        $updateFields['updated_at'] = date('Y-m-d H:i:s');
                        $updateFields['assigned_to'] = $assigned_to;

                        $this->Lead_model->update($matchedId, $updateFields);
                        $this->db->insert('crm_lead_activities', [
                            'lead_id'       => $matchedId,
                            'user_id'       => $currentUserId,
                            'activity_type' => 'note',
                            'notes'         => "Lead updated via spreadsheet import ('{$file['name']}' row #{$rowNum}).",
                            'occurred_at'   => date('Y-m-d H:i:s'),
                            'status'        => 'active',
                            'is_deleted'    => 0,
                            'created_at'    => date('Y-m-d H:i:s'),
                            'updated_at'    => date('Y-m-d H:i:s')
                        ]);
                        $updated++;
                    } elseif ($action === 'delete' || $action === 'delete_and_replace') {
                        $this->Lead_model->soft_delete($matchedId);

                        $ld['source']      = 'online';
                        $ld['lead_status'] = !empty($ld['lead_status']) ? $ld['lead_status'] : 'new';
                        $ld['status']      = 'active';
                        $ld['is_deleted']  = 0;
                        $ld['created_at']  = date('Y-m-d H:i:s');
                        $ld['updated_at']  = date('Y-m-d H:i:s');

                        $insertData = [];
                        foreach ($ld as $k => $v) {
                            if (in_array($k, $tableFields)) {
                                $insertData[$k] = ($v === '') ? null : $v;
                            }
                        }
                        $newId = $this->Lead_model->insert($insertData);
                        $this->db->insert('crm_lead_activities', [
                            'lead_id'       => $newId,
                            'user_id'       => $currentUserId,
                            'activity_type' => 'note',
                            'notes'         => "Replaced previous lead #{$matchedId} via spreadsheet import ('{$file['name']}' row #{$rowNum}).",
                            'occurred_at'   => date('Y-m-d H:i:s'),
                            'status'        => 'active',
                            'is_deleted'    => 0,
                            'created_at'    => date('Y-m-d H:i:s'),
                            'updated_at'    => date('Y-m-d H:i:s')
                        ]);
                        $deleted_and_replaced++;
                    }
                } else {
                    // New Unique Lead
                    $customer_id = null;
                    if ($company_name) {
                        $cust = $this->db->get_where('crm_customers', ['customer_org_name' => $company_name, 'is_deleted' => 0])->row_array();
                        if ($cust) {
                            $customer_id = (int)$cust['id'];
                        } elseif ($auto_create_customers) {
                            $cPhone = !empty($ld['corporate_phone']) ? $ld['corporate_phone'] : (!empty($ld['company_phone']) ? $ld['company_phone'] : '0000000000');
                            $this->db->insert('crm_customers', [
                                'customer_type'     => 'primary',
                                'customer_name'     => substr(trim($first_name . ' ' . $last_name) ?: $company_name, 0, 150),
                                'customer_org_name' => substr($company_name, 0, 150),
                                'phone'             => substr($cPhone, 0, 20),
                                'email'             => $email ?: null,
                                'address'           => $ld['company_address'] ?? ($ld['address'] ?? null),
                                'city'              => $ld['company_city'] ?? ($ld['city'] ?? null),
                                'state'             => $ld['company_state'] ?? ($ld['state'] ?? null),
                                'status'            => 'active',
                                'created_at'        => date('Y-m-d H:i:s'),
                                'updated_at'        => date('Y-m-d H:i:s'),
                            ]);
                            $customer_id = $this->db->insert_id();
                        }
                    }
                    $ld['customer_id'] = $customer_id;

                    $ld['source']      = 'online';
                    $ld['lead_status'] = !empty($ld['lead_status']) ? $ld['lead_status'] : 'new';
                    $ld['status']      = 'active';
                    $ld['is_deleted']  = 0;
                    $ld['created_at']  = date('Y-m-d H:i:s');
                    $ld['updated_at']  = date('Y-m-d H:i:s');

                    $insertData = [];
                    foreach ($ld as $k => $v) {
                        if (in_array($k, $tableFields)) {
                            $insertData[$k] = ($v === '') ? null : $v;
                        }
                    }

                    $this->Lead_model->insert($insertData);
                    $imported++;
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $dbErr = $this->db->error();
                $this->db->trans_rollback();
                $this->db->db_debug = $old_db_debug;
                $this->json_error('Database transaction failed while saving leads: ' . ($dbErr['message'] ?? 'Unknown error'));
            }

            $this->db->trans_commit();
            $this->db->db_debug = $old_db_debug;

            // Auto-sync product_id from product_demo column against crm_products
            $this->Lead_model->sync_products_from_product_demo();
        } catch (\Throwable $e) {
            $this->db->trans_rollback();
            $this->db->db_debug = $old_db_debug;
            log_message('error', 'Import confirm error: ' . $e->getMessage());
            $this->json_error('Import failed: ' . $e->getMessage());
        }

        $msgParts = [];
        if ($imported > 0)             $msgParts[] = "{$imported} new lead(s) imported";
        if ($updated > 0)              $msgParts[] = "{$updated} previous lead(s) overwritten";
        if ($deleted_and_replaced > 0) $msgParts[] = "{$deleted_and_replaced} previous lead(s) deleted & replaced";
        if ($skipped > 0)              $msgParts[] = "{$skipped} duplicate(s) skipped";

        $summaryMsg = !empty($msgParts) ? implode(', ', $msgParts) . '.' : 'Import completed.';

        $this->json_success([
            'imported'             => $imported,
            'updated'              => $updated,
            'deleted_and_replaced' => $deleted_and_replaced,
            'skipped'              => $skipped,
            'total_processed'      => $imported + $updated + $deleted_and_replaced + $skipped
        ], $summaryMsg);
    }

    public function import_process() {
        // Fallback / legacy support: automatically validate then confirm with 'skip'
        $this->import_validate();
    }
}
