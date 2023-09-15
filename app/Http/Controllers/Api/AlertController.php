<?php

namespace App\Http\Controllers\Api;

use App\ApiHelper\ApiResponseHelper;
use App\ApiHelper\Result;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alerts\CrateAlertRequest;
use App\Http\Requests\Alerts\GetAlertsListRequest;
use App\Http\Resources\Alerts\AlertResource;
use App\Http\Resources\PaginationResource;
use App\Services\Alerts\AlertService;


/**
 * @group Alerts
 * @authenticated
 * APIs for managing Alert Operations
 */
class AlertController extends Controller
{
    public function __construct(private AlertService $alertService)
    {
    }
    /**
     * Create New Alert For Employee
     *
     * This endpoint is used to Create New Alert For Employee in the Company and Admin Or Hr Can Access To This Api.
     *
     *
     * @bodyParam content string required Must not be greater than 100 characters Custom Example: test alert to this employeeee
     *
     * @bodyParam email email required Must Be Exists In Users Table Custom Example: mouaz@gmail.com
     *
     * @bodyParam type int optional The Type of the alert. Must be one of the following values:
     * - `1`: Swearing.
     * - `2`: Fabricate Problems.
     * - `3`: Others. Custom Example: 1
     *
     *
     * @response 200 scenario="Create New Alert For Employee"{
     * "data": {
     * "id": 3,
     * "email": "mouaz@gmail.com",
     * "content": "test alert to this employeeee",
     * "created_at": "2 seconds ago"
     * }
     * *}
     */
    public function store(CrateAlertRequest $request)
    {
        $createdData =  $this->alertService->create_alert($request->validated());
        if ($createdData['success']) {
            $alert = $createdData['data'];
            $returnData = AlertResource::make($alert);

            return ApiResponseHelper::sendResponse(
                new Result($returnData, "Done")
            );
        } else {
            return ['message' => $createdData['message']];
        }
    }
    /**
     * Show My Alerts List
     *
     * This endpoint is used to display the list of my alerts and authenticate employee access to this API. It will show the alerts specific to the authenticated employee.
     *
     * @response 200 scenario="Show My Alerts List"{
     *"data": [
     *{
     * "id": 3,
     * "email": "mouaz@gmail.com",
     * "content": "test alert to this employeeee",
     * "created_at": "2023-08-27"
     *}
     *]
     *}
     */

    public function getMyAlert(GetAlertsListRequest $request)
    {
        $data = $this->alertService->getMyAlerts($request->generateFilter());

        $returnData = AlertResource::collection($data);
        return ApiResponseHelper::sendResponse(
            new Result($returnData, "DONE")
        );
    }
}
