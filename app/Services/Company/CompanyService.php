<?php


namespace App\Services\Company;

use App\Interfaces\Comapny\CompanyServiceInterface;
use App\Models\Company;
use App\Repository\Company\CompanyRepository;
use App\Statuses\UserTypes;
use ParagonIE\Sodium\Compat;

class CompanyService implements CompanyServiceInterface
{

    public function __construct(private CompanyRepository $companyRepository)
    {
    }
    public function create_company($data)
    {
        return $this->companyRepository->create_company($data);
    }
    public function add_commercial_record($data)
    {
        return $this->companyRepository->add_commercial_record($data);
    }



    public function update_company($data)
    {
        return $this->companyRepository->update_company($data);
    }


    public function update_commercial_record($data)
    {
        return $this->companyRepository->update_commercial_record($data);
    }


    public function show($id)
    {
        if (auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $id || auth()->user()->type == UserTypes::SUPER_ADMIN) {

            return ['success' => true, 'data' => $this->companyRepository->with('admin', 'locations')->getById($id)];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }

    public function show_percenatge_company()
    {
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {
            $company = Company::where('id', auth()->user()->company_id)->first();
            return ['success' => true, 'data' => $company];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }


    public function update_percentage()
    {
        $company = Company::where('id', auth()->user()->company_id)->first();
        if (auth()->user()->type == UserTypes::ADMIN  || auth()->user()->type == UserTypes::HR && $company->id == auth()->user()->company_id) {

            if ($company->percentage == false) {
                $company->update([
                    'percentage' => true
                ]);
            } else {
                $company->update([
                    'percentage' => false
                ]);
            }
            return ['success' => true, 'data' =>  $company];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }
}
