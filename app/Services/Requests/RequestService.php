<?php

namespace App\Services\Requests;

use App\Filter\VacationRequests\RequestFilter;
use App\Models\Request;
use App\Query\Employee\GetMonthlyShiftQuery;
use App\Repository\Requests\RequestRepository;
use App\Statuses\RequestStatus;
use App\Statuses\UserTypes;

class RequestService
{
    public function __construct(private RequestRepository $requestRepository, private GetMonthlyShiftQuery $getMonthlyShiftQuery)
    {
    }

    public function add_vacation_request($data)
    {
        return $this->requestRepository->add_vacation_request($data);
    }

    public function add_justify_request($data)
    {
        return $this->requestRepository->add_justify_request($data);
    }
    public function add_retirement_request($data)
    {
        return $this->requestRepository->add_retirement_request($data);
    }
    public function add_resignation_request($data)
    {
        return $this->requestRepository->add_resignation_request($data);
    }



    public function show($id)
    {
        $request = Request::where('id', $id)->first();
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR && auth()->user()->company_id == $request->company_id) {
            return ['success' => true, 'data' => $this->requestRepository->with('user')->getById($id)];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }

    public function approve_request($id)
    {
        $request = Request::where('id', $id)->first();
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR && auth()->user()->company_id == $request->company_id) {
            $vacationAfterAccept = $this->requestRepository->getById($id);
            $vacationAfterAccept->status = RequestStatus::APPROVEED;
            $vacationAfterAccept->update();

            return ['success' => true, 'data' => $vacationAfterAccept->load('user')];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }
    public function reject_request($data)
    {
        return $this->requestRepository->reject_request($data);
    }

    public function my_requests(RequestFilter $requestFilter = null)
    {
        if ($requestFilter != null) {
            return $this->requestRepository->my_requests($requestFilter);
        } else {
            return $this->requestRepository->paginate();
        }
    }
    public function my_approved_vacations_requests()
    {

        return $this->requestRepository->my_approved_vacations_requests();
    }


    public function vacation_requests()
    {

        return $this->requestRepository->vacation_requests();
    }
    public function justify_requests()
    {

        return $this->requestRepository->justify_requests();
    }
    public function retirement_requests()
    {

        return $this->requestRepository->retirement_requests();
    }
    public function resignation_requests()
    {
        return $this->requestRepository->resignation_requests();
    }
    public function getMonthlyData($filter)
    {
        return $this->getMonthlyShiftQuery->getMonthlyData($filter);
    }
    public function all_requests()
    {
        return $this->requestRepository->all_requests();
    }
}
