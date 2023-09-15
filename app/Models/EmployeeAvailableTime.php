<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAvailableTime extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'daily_annual', 'hourly_annual', 'company_id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
