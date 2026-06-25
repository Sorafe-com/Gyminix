<?php

namespace App\Models;

use CodeIgniter\Model;

class GymModel extends Model
{
    protected $table            = 'gyms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name', 'legal_name', 'tax_id', 'address', 'phone', 'email',
        'website', 'logo', 'primary_color', 'secondary_color',
        'currency', 'currency_symbol', 'timezone', 'language',
        'date_format', 'status',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
