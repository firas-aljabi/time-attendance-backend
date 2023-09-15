<?php

namespace App\Models;

use App\Statuses\UserTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'commercial_record', 'start_commercial_record', 'end_commercial_record', 'percentage'];


    public function employees()
    {
        return $this->hasMany(User::class)->where('type', UserTypes::EMPLOYEE);
    }

    public function admin()
    {
        return $this->hasOne(User::class)->where('type', UserTypes::ADMIN);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }




    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function percentage()
    {
        return $this->hasMany(Percentage::class);
    }


    public function JustifyRequests()
    {
        return $this->hasMany(JustifyRequest::class);
    }
    public function VacationRequests()
    {
        return $this->hasMany(VacationRequest::class);
    }

    public function EmployeeAvailableTime()
    {
        return $this->belongsTo(EmployeeAvailableTime::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
