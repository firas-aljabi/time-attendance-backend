<?php

namespace App\Repository\Company;

use App\Http\Trait\UploadImage;
use App\Models\Company;
use App\Models\Location;
use App\Repository\BaseRepositoryImplementation;
use App\Statuses\UserTypes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CompanyRepository extends BaseRepositoryImplementation
{
    use UploadImage;

    public function getFilterItems($filter)
    {
        $records = Company::query();
        $records->when(isset($filter->orderBy), function ($records) use ($filter) {
            $records->orderBy($filter->getOrderBy(), $filter->getOrder());
        });
        return $records->paginate($filter->per_page);
    }

    public function create_company($data)
    {
        DB::beginTransaction();
        try {

            if (auth()->user()->type == UserTypes::SUPER_ADMIN) {

                $company = new Company();
                $company->name = $data['name'];
                $company->email = $data['email'];
                $company->save();

                Location::create([
                    'company_id' => $company->id,
                    'longitude' => $data['longitude'],
                    'latitude' => $data['latitude'],
                    'radius' => $data['radius'],
                ]);

                DB::commit();
                if ($company === null) {
                    return ['success' => false, 'message' => "Company was not created"];
                }
                return ['success' => true, 'data' => $company->load(['admin', 'locations'])];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function add_commercial_record($data)
    {
        DB::beginTransaction();
        try {

            if (auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $data['company_id']) {

                $company = $this->getById($data['company_id']);

                $file = Arr::get($data, 'commercial_record');
                $file_name = $this->uploadCompanyAttachment($file);
                $company->commercial_record = $file_name;

                $company->start_commercial_record = $data['start_commercial_record'];
                $company->end_commercial_record = $data['end_commercial_record'];
                $company->save();

                DB::commit();
                if ($company === null) {
                    return ['success' => false, 'message' => "commercial record was not Added"];
                }
                return ['success' => true, 'data' => $company->load(['admin', 'locations'])];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function update_company($data)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::SUPER_ADMIN) {
                $company = $this->updateById($data['company_id'], $data);
                $location = Location::where('company_id', $data['company_id']);
                if (isset($data['longitude'])) {
                    $location->update([
                        'longitude' => $data['longitude']
                    ]);
                }
                if (isset($data['latitude'])) {
                    $location->update([
                        'latitude' => $data['latitude']
                    ]);
                }
                if (isset($data['radius'])) {
                    $location->update([
                        'radius' => $data['radius']
                    ]);
                }

                DB::commit();
                if ($company === null) {
                    return ['success' => false, 'message' => "Company was not Updated"];
                }
                return ['success' => true, 'data' => $company->load('admin', 'locations')];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update_commercial_record($data)
    {
        DB::beginTransaction();
        try {


            if (auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $data['company_id']) {
                $company = $this->updateById($data['company_id'], $data);
                if (Arr::has($data, 'commercial_record')) {
                    $file = Arr::get($data, 'commercial_record');
                    $file_name = $this->uploadCompanyAttachment($file);
                    $company->commercial_record = $file_name;
                }

                DB::commit();
                if ($company === null) {
                    return ['success' => false, 'message' => "Commercial Record was not Updated"];
                }
                return ['success' => true, 'data' => $company->load('admin', 'locations')];
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function model()
    {
        return Company::class;
    }
}
