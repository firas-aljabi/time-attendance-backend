<?php

namespace App\Http\Controllers\Api;

use App\ApiHelper\ApiResponseHelper;
use App\ApiHelper\Result;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\AddCommercialRecordRequeat;
use App\Http\Requests\Company\CreateComapnyRequest;
use App\Http\Requests\Company\UpdateCommercialRecordRequeat;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\Company\ComapnyResource;
use App\Http\Resources\Company\CompanyPercentageResource;
use App\Services\Company\CompanyService;


/**
 * @group Companies
 * @authenticated
 * APIs for managing Companies
 */
class CompanyController extends Controller
{

    public function __construct(private CompanyService $companyService)
    {
    }


    /**
     * Create Company
     *
     * This endpoint is used to create a new company. Only super admins can access this API.
     *
     * @bodyParam name string required The name of the company. Custom  Example: Goma Company
     * @bodyParam email email required The email address of the company. Custom  Example: goma@goma.com
     * @bodyParam longitude number required The longitude of the company location. Custom  Example: 25.12
     * @bodyParam latitude number required The latitude of the company location. Custom Example: 15.32
     * @bodyParam radius number required The radius of the company location. Custom Example: 15
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "name": "Goma Company",
     *         "email": "goma@goma.com",
     *         "commercial_record": null,
     *         "start_commercial_record": null,
     *         "end_commercial_record": null,
     *         "locations": [
     *             {
     *                 "id": 1,
     *                 "Longitude": "25.12",
     *                 "Latitude": "15.32",
     *                 "Radius": "21"
     *             }
     *         ],
     *         "admin": null
     *     }
     * }
     */
    public function store(CreateComapnyRequest $request)
    {
        $createdData =  $this->companyService->create_company($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ComapnyResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Add Commercal To Company
     *
     * This endpoint is used to create a new company. Only super admins can access this API.
     * @bodyParam company_id int required The ID of the company. Must exist in the companies table.
     * @bodyParam start_commercial_record date The start date of the commercial record. Custom Example: 2023-08-27
     * @bodyParam end_commercial_record date The end date of the commercial record. Custom  Example: 2023-08-27
     * @bodyParam commercial_record file The commercial record file. Must not be greater than 5120 kilobytes.
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "name": "Goma Company",
     *         "email": "goma@goma.com",
     *         "commercial_record": "http://127.0.0.1:8000/companies/2023-09-05-Company-2.png",
     *         "start_commercial_record": "2023-02-01",
     *         "end_commercial_record": "2023-09-01",
     *         "locations": [
     *             {
     *                 "id": 1,
     *                 "Longitude": "25.12",
     *                 "Latitude": "15.32",
     *                 "Radius": "21"
     *             }
     *         ],
     *         "admin": null
     *     }
     * }
     */
    public function add_commercial_record(AddCommercialRecordRequeat $request)
    {
        $createdData =  $this->companyService->add_commercial_record($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ComapnyResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Show Comapny
     *
     * This endpoint is used to display company and authenticate admin access to this API. It will show company specific to the authenticated admin Or Super Admin.
     *
     * @urlParam id int required Must Be Exists In companies Table
     *
     * @response 201 scenario="Show Comapny"{
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "name": "Goma Company",
     *         "email": "goma@goma.com",
     *         "commercial_record": "http://127.0.0.1:8000/companies/2023-09-05-Company-2.png",
     *         "start_commercial_record": "2023-02-01",
     *         "end_commercial_record": "2023-09-01",
     *         "locations": [
     *             {
     *                 "id": 1,
     *                 "Longitude": "25.12",
     *                 "Latitude": "15.32",
     *                 "Radius": "21"
     *             }
     *         },
     *         "admin": {
     *                    "id": 2,
     *                    "name": "Firas Jabi"
     *                  }
     *            }
     *      }
     */
    public function show($id)
    {
        $company = $this->companyService->show($id);

        if ($company['success']) {
            $alert = $company['data'];
            $returnData = ComapnyResource::make($alert);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $company['message']];
        }
    }

    /**
     * Show Company Percentage
     *
     * This endpoint is used to display the company percentage. Only authenticated admins or HR personnel can access this API. It will show the percentage specific to the authenticated admin or HR personnel.
     *
     * @response 200 {
     *     "data": {
     *         "percentage": false
     *     }
     * }
     */
    public function show_percenatge_company()
    {
        $company = $this->companyService->show_percenatge_company();

        if ($company['success']) {
            $data = $company['data'];
            $returnData = CompanyPercentageResource::make($data);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $company['message']];
        }
    }

    /**
     * Update Company Percentage
     *
     * This endpoint is used to Update the company percentage. Only authenticated admins or HR personnel can access this API. It will Update the percentage specific to the authenticated admin or HR personnel.
     *
     * @response 200 {
     *     "data": {
     *         "percentage": true
     *     }
     * }
     */
    public function update_percentage()
    {
        $company = $this->companyService->update_percentage();

        if ($company['success']) {
            $alert = $company['data'];
            $returnData = CompanyPercentageResource::make($alert);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $company['message']];
        }
    }


    /**
     * Update Company
     *
     * This endpoint is used to update a company. Only super admins can access this API.
     *
     * @bodyParam company_id int required The ID of the company. Must exist in the companies table.
     * @bodyParam name string required The name of the company. Custom Example: Goma Company
     * @bodyParam email email required The email address of the company.Custom Example: goma@goma.com
     * @bodyParam longitude number required The longitude of the company location. Custom  Example: 25.12
     * @bodyParam latitude number required The latitude of the company location. Custom Example: 15.32
     * @bodyParam radius number required The radius of the company location. Custom Example: 15
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "name": "Goma Company",
     *         "email": "goma@goma.com",
     *         "commercial_record": null,
     *         "start_commercial_record": "2023-02-01",
     *         "end_commercial_record": "2023-09-01",
     *         "locations": [
     *             {
     *                 "id": 1,
     *                 "Longitude": "25.12",
     *                 "Latitude": "15.32",
     *                 "Radius": "21"
     *             }
     *         ],
     *         "admin": null
     *     }
     * }
     */
    public function update_comapny(UpdateCompanyRequest $request)
    {
        $createdData =  $this->companyService->update_company($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ComapnyResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }

    /**
     * Update Commercal Record in Company
     *
     * This endpoint is used to update commercal record and authenticate admin access to this API. It will update commercal comapny to the authenticated admin.
     *
     * @bodyParam company_id int required The ID of the company. Must exist in the companies table.
     * @bodyParam start_commercial_record date The start date of the commercial record. Custom Example: 2023-08-27
     * @bodyParam end_commercial_record date The end date of the commercial record. Custom  Example: 2023-08-27
     * @bodyParam commercial_record file The commercial record file. Must not be greater than 5120 kilobytes.
     *
     * @response 200 {
     *     "data": {
     *         "id": 1,
     *         "name": "Goma Company",
     *         "email": "goma@goma.com",
     *         "commercial_record": "http://127.0.0.1:8000/companies/2023-09-05-Company-2.png",
     *         "start_commercial_record": "2023-02-01",
     *         "end_commercial_record": "2023-09-01",
     *         "locations": [
     *             {
     *                 "id": 1,
     *                 "Longitude": "25.12",
     *                 "Latitude": "15.32",
     *                 "Radius": "21"
     *             }
     *         ],
     *         "admin": null
     *     }
     * }
     */
    public function update_commercial_record(UpdateCommercialRecordRequeat $request)
    {
        $createdData =  $this->companyService->update_commercial_record($request->validated());

        if ($createdData['success']) {
            $newData = $createdData['data'];
            $returnData = ComapnyResource::make($newData);
            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
}
