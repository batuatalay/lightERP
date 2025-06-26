<?php
class CompanyModel extends BaseORM {
    protected static $table = 'companies';
    protected static $primaryKey = 'company_id';
    protected static $fillable = [
        'organization_id',
        'code',
        'name', 
        'tax_number',
        'tax_office',
        'address',
        'city',
        'discount',
        'status'
    ];
    
    public static function getAllCompaniesWithContacts($organizationId = null) {
        $sql = "SELECT " .
                "cmp.company_id, " .
                "cmp.code as company_code, " .
                "cmp.name as company_name, " .
                "cmp.tax_number, " .
                "cmp.city, " .
                "cmp.discount, " .
                "cmp.status, " .
                "cnt.name as contact_name, " .
                "cnt.phone as contact_phone, " .
                "cnt.mail as contact_mail, " .
                "cnt.title as contact_title " .
                "FROM " . static::$table . " as cmp " .
                "LEFT JOIN contacts as cnt ON cmp.company_id = cnt.company_id";
        
        $params = [];
        if ($organizationId) {
            $sql .= " WHERE cmp.organization_id = ?";
            $params[] = $organizationId;
        }
        
        $sql .= " ORDER BY cmp.name, cnt.name";
        return static::raw($sql, $params);
    }
    
    
    public static function getAllCompanies($organizationId = null) {
        if ($organizationId) {
            return static::raw("
                SELECT * FROM companies 
                WHERE organization_id = ? AND status = 'active'
                ORDER BY name
            ", [$organizationId]);
        } else {
            return static::raw("
                SELECT * FROM companies 
                WHERE status = 'active'
                ORDER BY name
            ");
        }
    }
    
    
    public static function getCompaniesWithContactCount($organizationId = null) {
        $sql = "SELECT " .
                "cmp.*, " .
                "COUNT(cnt.contact_id) as contact_count " .
                "FROM companies cmp " .
                "LEFT JOIN contacts cnt ON cmp.company_id = cnt.company_id AND cnt.status = 'active'";
        
        $params = [];
        
        if ($organizationId) {
            $sql .= " WHERE cmp.organization_id = ?";
            $params[] = $organizationId;
        }
        
        $sql .= " GROUP BY cmp.company_id ORDER BY cmp.name";
        
        return static::raw($sql, $params);
    }
    
    
    public function getContacts() {
        return static::raw("
            SELECT * FROM contacts 
            WHERE company_id = ? AND status = 'active'
            ORDER BY name
        ", [$this->company_id]);
    }
    
    
    protected function create() {
        
        if (!isset($this->attributes['code'])) {
            $this->attributes['code'] = $this->generateCompanyCode();
        }
        
        return parent::create();
    }
    
    
    private function generateCompanyCode() {
        // Organization prefix'i al
        $orgPrefix = 'COMP';
        
        // Son company kodunu bul
        $lastCode = static::raw("
            SELECT code FROM companies 
            WHERE code LIKE ? 
            ORDER BY code DESC 
            LIMIT 1
        ", ["{$orgPrefix}-%"]);
        
        if (!empty($lastCode)) {
            // Son numarayı çıkar ve 1 artır
            $lastNumber = intval(substr($lastCode[0]['code'], strlen($orgPrefix) + 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $orgPrefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    
    
    public static function findByCode($code) {
        $results = static::where('code', $code);
        return !empty($results) ? $results[0] : null;
    }
    
    
    public static function searchCompanies($query, $organizationId = null) {
        $sql = "SELECT * FROM companies 
                WHERE (name LIKE ? OR code LIKE ? OR tax_number LIKE ?)";
        
        $params = ["%{$query}%", "%{$query}%", "%{$query}%"];
        
        if ($organizationId) {
            $sql .= " AND organization_id = ?";
            $params[] = $organizationId;
        }
        
        $sql .= " ORDER BY name";
        
        return static::raw($sql, $params);
    }
    
    
    public function softDelete() {
        // Companies tablosunda deleted_at yok, status kullan
        $this->status = 'inactive';
        return $this->save();
    }
    
    
    public static function getActiveCompanies($organizationId = null) {
        if ($organizationId) {
            return static::where('organization_id', $organizationId)
                        ->where('status', 'active')
                        ->get();
        } else {
            return static::where('status', 'active')->get();
        }
    }
}