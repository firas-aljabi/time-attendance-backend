<?php

namespace App\Repository\Admin;

use App\Filter\Attendance\AttendanceFilter;
use App\Filter\Attendance\AttendanceOverviewFilter;
use App\Filter\Employees\EmployeeFilter;
use App\Filter\Employees\LeaveCalendarFilter;
use App\Filter\Nationalalities\NationalFilter;
use App\Filter\Salary\SalaryFilter;
use App\Http\Trait\UploadImage;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Deposit;
use App\Models\EmployeeAvailableTime;
use App\Models\Holiday;
use App\Models\Location;
use App\Models\Nationalitie;
use App\Models\Notification;
use App\Models\Percentage;
use App\Models\Request;
use App\Models\Salary;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\TowFactor;
use App\Repository\BaseRepositoryImplementation;
use App\Services\Email\EmailService;
use App\Services\Notifications\NotificationService;
use App\Statuses\AdversariesType;
use App\Statuses\DepositStatus;
use App\Statuses\EmployeeStatus;
use App\Statuses\HolidayTypes;
use App\Statuses\PermissionType;
use App\Statuses\RequestStatus;
use App\Statuses\RequestType;
use App\Statuses\RewardsType;
use App\Statuses\TerminateTime;
use App\Statuses\TerminateType;
use App\Statuses\UserTypes;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminRepository extends BaseRepositoryImplementation
{
    use UploadImage;
    public function getFilterItems($filter)
    {
        $records = User::query()->where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('status', '<>', EmployeeStatus::DISMISSED);
        if ($filter instanceof EmployeeFilter) {

            $records->when(isset($filter->name), function ($records) use ($filter) {
                $records->where('name', 'LIKE', '%' . $filter->getName() . '%');
            });

            $records->when(isset($filter->orderBy), function ($records) use ($filter) {
                $records->orderBy($filter->getOrderBy(), $filter->getOrder());
            });


            return $records->get();
        }
        return $records->get();
    }

    public function getEmployeesDismissedList($filter)
    {
        $records = User::query()->where('type', UserTypes::EMPLOYEE)->where('company_id', auth()->user()->company_id)->where('status',  EmployeeStatus::DISMISSED);
        if ($filter instanceof EmployeeFilter) {
            $records->when(isset($filter->name), function ($records) use ($filter) {
                $records->where('name', 'LIKE', '%' . $filter->getName() . '%');
            });
            $records->when(isset($filter->orderBy), function ($records) use ($filter) {
                $records->orderBy($filter->getOrderBy(), $filter->getOrder());
            });
            return $records->get();
        }
        return $records->get();
    }

    public function getSalaryFilterItems($filter)
    {
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {
            $records = Salary::query()->where('company_id', auth()->user()->company_id);
            if ($filter instanceof SalaryFilter) {

                $records->when(isset($filter->orderBy), function ($records) use ($filter) {
                    $records->orderBy($filter->getOrderBy(), $filter->getOrder());
                });

                $salaries = $records->with('user')->get();
                return ['success' => true, 'data' => $salaries];
            }
            $salaries = $records->with('user')->get();
            return ['success' => true, 'data' => $salaries];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }

    public function list_of_nationalities($filter)
    {
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {
            $records = Nationalitie::query();
            return $records->get();
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }


    public function leave_calendar($filter)
    {
        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {
            $records = Request::query()->where('company_id', auth()->user()->company_id)->where('type', RequestType::JUSTIFY)->orWhere('type', RequestType::VACATION)->where('status', RequestStatus::APPROVEED)->with('user');
            if ($filter instanceof LeaveCalendarFilter) {
                $records->when(isset($filter->date), function ($records) use ($filter) {
                    $date = $filter->getDate();
                    $records->where(function ($query) use ($date) {
                        $query->whereDate('date', $date)
                            ->orWhereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date);
                    });
                });

                $dayType = $filter->getDay();
                if ($dayType == 1) {
                    $records->where(function ($query) {
                        $query->whereDate('date', now()->toDateString())
                            ->orWhere(function ($query) {
                                $query->whereDate('start_date', '<=', now()->toDateString())
                                    ->whereDate('end_date', '>=', now()->toDateString());
                            });
                    });
                } else if ($dayType == 2) {
                    $records->where(function ($query) {
                        $query->whereDate('date', now()->addDay()->toDateString())
                            ->orWhere(function ($query) {
                                $query->whereDate('start_date', '<=', now()->addDay()->toDateString())
                                    ->whereDate('end_date', '>=', now()->addDay()->toDateString());
                            });
                    });
                }

                return ['success' => true, 'data' => $records->get()];
            }
            return ['success' => true, 'data' => $records->get()];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }

    public function employees_attendances($filter)
    {
        if (auth()->user()->type == UserTypes::ADMIN) {

            $records = User::query()->where('type', UserTypes::HR)->where('company_id', auth()->user()->company_id);
            $records = Attendance::query()->whereMonth('date', Carbon::now()->month);
            if ($filter instanceof AttendanceFilter) {

                $records->when(isset($filter->orderBy), function ($records) use ($filter) {
                    $records->orderBy($filter->getOrderBy(), $filter->getOrder());
                });

                $attendances = $records->with('user')->paginate($filter->per_page);
                return ['success' => true, 'data' => $attendances];
            }
            $attendances = $records->with('user')->paginate($filter->per_page);
            return ['success' => true, 'data' => $attendances];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }
    public function get_contract_expiration($filter)
    {

        if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {
            $records = Contract::where('end_contract_date', '<=', Carbon::now()->addMonth())->whereNotNull('end_contract_date')->get();
            return ['success' => true, 'data' => $records->load('user')];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }



    public function getHrsList()
    {
        $records = User::query()->where('type', UserTypes::HR)->where('company_id', auth()->user()->company_id);
        return $records->get();
    }

    public function create_employee($data)
    {
        DB::beginTransaction();

        try {

            if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::ADMIN) {
                $user = new User();

                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = Hash::make($data['password']);
                $user->departement = $data['departement'];
                $user->skills = $data['skills'];
                $user->position = $data['position'];
                $user->gender = $data['gender'];
                $user->status = EmployeeStatus::ABSENT;
                $user->phone = $data['phone'];
                $user->company_id = auth()->user()->company_id;
                $user->serial_number = $data['serial_number'];
                $user->work_email = $data['work_email'];
                $user->mobile = $data['mobile'];
                $user->nationalitie_id = $data['nationalitie_id'];
                $user->birthday_date = $data['birthday_date'];
                $user->material_status = $data['material_status'];
                $user->address = $data['address'];
                $user->guarantor = $data['guarantor'];
                $user->branch = $data['branch'];
                $user->start_job_contract = $data['start_job_contract'];
                $user->end_job_contract = $data['end_job_contract'];

                if (Arr::has($data, 'image')) {
                    $file = Arr::get($data, 'image');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->image = $file_name;
                }

                if (Arr::has($data, 'id_photo')) {
                    $file = Arr::get($data, 'id_photo');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->id_photo = $file_name;
                }
                if (Arr::has($data, 'biography')) {
                    $file = Arr::get($data, 'biography');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->biography = $file_name;
                }
                if (Arr::has($data, 'visa')) {
                    $file = Arr::get($data, 'visa');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->visa = $file_name;
                }
                if (Arr::has($data, 'municipal_card')) {
                    $file = Arr::get($data, 'municipal_card');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->municipal_card = $file_name;
                }
                if (Arr::has($data, 'health_insurance')) {
                    $file = Arr::get($data, 'health_insurance');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->health_insurance = $file_name;
                }
                if (Arr::has($data, 'passport')) {
                    $file = Arr::get($data, 'passport');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->passport = $file_name;
                }
                if (Arr::has($data, 'employee_sponsorship')) {
                    $file = Arr::get($data, 'employee_sponsorship');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->employee_sponsorship = $file_name;
                }
                if (Arr::has($data, 'employee_residence')) {
                    $file = Arr::get($data, 'employee_residence');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->employee_residence = $file_name;
                }

                if ($data['end_visa'] != null) {

                    $user->end_visa = $data['end_visa'];
                }

                if ($data['end_passport'] != null) {
                    $user->end_passport = $data['end_passport'];
                }

                if ($data['end_employee_sponsorship'] != null) {
                    $user->end_employee_sponsorship = $data['end_employee_sponsorship'];
                }

                if ($data['end_municipal_card'] != null) {
                    $user->end_municipal_card = $data['end_municipal_card'];
                }

                if ($data['end_health_insurance'] != null) {
                    $user->end_health_insurance = $data['end_health_insurance'];
                }

                if ($data['end_employee_residence'] != null) {
                    $user->end_employee_residence = $data['end_employee_residence'];
                }
                $user->basic_salary = $data['basic_salary'];
                $user->type = UserTypes::EMPLOYEE;

                if ($data['entry_time'] != null) {
                    $user->entry_time = $data['entry_time'];
                }
                if ($data['leave_time'] != null) {
                    $user->leave_time = $data['leave_time'];
                }
                $user->save();


                $salary = Salary::create([
                    'user_id' => $user->id,
                    'salary' => $user->basic_salary,
                    'basic_salary' => $user->basic_salary,
                    'rewards' => 0,
                    'adversaries' => 0,
                    'housing_allowance' => 0,
                    'transportation_allowance' => 0,
                    'company_id' => auth()->user()->company_id,
                    'date' => date('Y-m-d'),
                ]);

                if (isset($data['housing_allowance']) && isset($data['transportation_allowance'])) {
                    $salary->update([
                        'transportation_allowance' => $data['transportation_allowance'],
                        'housing_allowance' => $data['housing_allowance'],
                        'salary' =>  $salary->salary + $data['transportation_allowance'] + $data['housing_allowance'],
                        'date' => date('Y-m-d'),
                    ]);
                } elseif (isset($data['housing_allowance'])) {
                    $salary->update([
                        'housing_allowance' => $data['housing_allowance'],
                        'salary' =>  $salary->salary + $data['housing_allowance'],
                        'date' => date('Y-m-d'),
                    ]);
                } elseif (isset($data['transportation_allowance'])) {
                    $salary->update([
                        'transportation_allowance' => $data['transportation_allowance'],
                        'salary' =>  $salary->salary + $data['transportation_allowance'],
                        'date' => date('Y-m-d'),
                    ]);
                }

                Contract::create([
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'start_contract_date' => $data['start_job_contract'],
                    'end_contract_date' =>  $data['end_job_contract'],

                ]);
                if ((isset($data['number_of_shifts'])) && !empty($data['shifts'])) {
                    foreach ($data['shifts'] as $shift) {
                        Shift::create([
                            'user_id' => $user->id,
                            'company_id' => $user->company_id,
                            'start_time' => $shift['start_time'],
                            'end_time' => $shift['end_time'],
                            'start_break_hour' => $shift['start_break_hour'],
                            'end_break_hour' => $shift['end_break_hour'],
                        ]);
                    }
                }

                if (isset($data['hourly_annual']) && isset($data['daily_annual'])) {
                    $employeeAvailableTime = new EmployeeAvailableTime();
                    $employeeAvailableTime->user_id = $user->id;
                    $employeeAvailableTime->hourly_annual = $data['hourly_annual'];
                    $employeeAvailableTime->daily_annual = $data['daily_annual'];
                    $employeeAvailableTime->company_id = $user->company_id;
                    $employeeAvailableTime->save();
                } elseif (isset($data['hourly_annual'])) {
                    $employeeAvailableTime = new EmployeeAvailableTime();
                    $employeeAvailableTime->user_id = $user->id;
                    $employeeAvailableTime->hourly_annual = $data['hourly_annual'];
                    $employeeAvailableTime->company_id = $user->company_id;
                    $employeeAvailableTime->save();
                } elseif (isset($data['daily_annual'])) {
                    $employeeAvailableTime = new EmployeeAvailableTime();
                    $employeeAvailableTime->user_id = $user->id;
                    $employeeAvailableTime->daily_annual = $data['daily_annual'];
                    $employeeAvailableTime->company_id = $user->company_id;
                    $employeeAvailableTime->save();
                }
                $user->generate_code();
                $userCode = $user->code;
                $userEmail = $user->email;
                $userPassword = $data['password'];
                $recipientEmail = $userEmail;
                $company = Company::where('id', auth()->user()->company_id)->first();
                $companyName = $company->name;
                EmailService::sendHtmlEmail($recipientEmail, $companyName, $userCode, $userEmail, $userPassword);
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }

            DB::commit();

            if ($user === null) {
                return ['success' => false, 'message' => "User was not created"];
            }

            return ['success' => true, 'data' => $user->load('nationalitie', 'shifts', 'deposits')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update_employee($data)
    {
        DB::beginTransaction();
        $user = $this->getById(auth()->user()->id);

        try {

            if (auth()->user()->type == UserTypes::EMPLOYEE || auth()->user()->id == $user->id) {

                $newData = $this->updateById(auth()->user()->id, $data);

                if (Arr::has($data, 'image')) {
                    $file = Arr::get($data, 'image');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $newData->image = $file_name;
                }
                $newData->save();
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();

            if ($newData === null) {
                return ['success' => false, 'message' => "User was not Updated"];
            }

            return ['success' => true, 'data' => $newData->load('nationalitie', 'shifts', 'deposits')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function admin_update_employee($data)
    {
        DB::beginTransaction();

        $currentMonth = Carbon::now()->format('Y-m');

        $salary = Salary::where('user_id', $data['user_id'])->whereDate('created_at', 'like', $currentMonth . '%')->first();
        try {
            if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR) {
                $user = $this->updateById($data['user_id'], $data);
                if (isset($data['housing_allowance']) && isset($data['transportation_allowance'])) {
                    $salary->update([
                        'transportation_allowance' => $data['transportation_allowance'],
                        'housing_allowance' => $data['housing_allowance'],
                        'salary' =>  $salary->salary + $data['transportation_allowance'] + $data['housing_allowance'],
                        'date' => date('Y-m-d'),
                    ]);
                } elseif (isset($data['housing_allowance'])) {
                    $salary->update([
                        'housing_allowance' => $data['housing_allowance'],
                        'salary' =>  $salary->salary + $data['housing_allowance'],
                        'date' => date('Y-m-d'),
                    ]);
                } elseif (isset($data['transportation_allowance'])) {
                    $salary->update([
                        'transportation_allowance' => $data['transportation_allowance'],
                        'salary' =>  $salary->salary + $data['transportation_allowance'],
                        'date' => date('Y-m-d'),
                    ]);
                }

                if (Arr::has($data, 'id_photo')) {
                    $file = Arr::get($data, 'id_photo');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'id_photo');

                    $user->id_photo = $file_name;
                }
                if (Arr::has($data, 'biography')) {
                    $file = Arr::get($data, 'biography');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'biography');
                    $user->biography = $file_name;
                }
                if (Arr::has($data, 'visa')) {
                    $file = Arr::get($data, 'visa');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'visa');
                    $user->visa = $file_name;
                }
                if (Arr::has($data, 'municipal_card')) {
                    $file = Arr::get($data, 'municipal_card');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'municipal_card');
                    $user->municipal_card = $file_name;
                }
                if (Arr::has($data, 'health_insurance')) {
                    $file = Arr::get($data, 'health_insurance');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'health_insurance');
                    $user->health_insurance = $file_name;
                }
                if (Arr::has($data, 'passport')) {
                    $file = Arr::get($data, 'passport');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'passport');
                    $user->passport = $file_name;
                }
                if (Arr::has($data, 'employee_sponsorship')) {
                    $file = Arr::get($data, 'employee_sponsorship');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'employee_sponsorship');
                    $user->employee_sponsorship = $file_name;
                }
                if (Arr::has($data, 'employee_residence')) {
                    $file = Arr::get($data, 'employee_residence');
                    $file_name = $this->deleteAndUploadEmployeeAttachment($file, $user->id, 'employee_residence');
                    $user->employee_residence = $file_name;
                }
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();
            if ($user === null) {
                return ['success' => false, 'message' => "User was not Updated"];
            }
            return ['success' => true, 'data' => $user->load('salaries')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function update_employee_permission_time($data)
    {
        DB::beginTransaction();
        $user = $this->getById($data['user_id']);

        try {

            if (auth()->user()->type == UserTypes::HR || (auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $user->company_id)) {
                if (isset($data['entry_time']) && !isset($data['leave_time'])) {
                    $user->update([
                        'entry_time' => $data['entry_time']
                    ]);
                } elseif (isset($data['leave_time']) && !isset($data['entry_time'])) {
                    $user->update([
                        'leave_time' => $data['leave_time']
                    ]);
                } elseif (isset($data['leave_time']) && isset($data['entry_time'])) {
                    return ['success' => false, 'message' => "Choose Entry Time Or Leave Time Not Together"];
                }
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();

            if ($user === null) {
                return ['success' => false, 'message' => "User was not Updated"];
            }

            return ['success' => true, 'data' => $user];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function create_hr($data)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::ADMIN) {
                $user = new User();
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = Hash::make($data['password']);
                $user->departement = $data['departement'];
                $user->skills = $data['skills'];
                $user->gender = $data['gender'];
                $user->status = EmployeeStatus::ACTIVE;
                $user->phone = $data['phone'];
                $user->serial_number = $data['serial_number'];
                $user->work_email = $data['work_email'];
                $user->mobile = $data['mobile'];
                $user->nationalitie_id = $data['nationalitie_id'];
                $user->birthday_date = $data['birthday_date'];
                $user->material_status = $data['material_status'];
                $user->address = $data['address'];
                $user->guarantor = $data['guarantor'];
                $user->branch = $data['branch'];
                $user->start_job_contract = $data['start_job_contract'];
                $user->end_job_contract = $data['end_job_contract'];
                if (Arr::has($data, 'image')) {
                    $file = Arr::get($data, 'image');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->image = $file_name;
                }

                if (Arr::has($data, 'id_photo')) {
                    $file = Arr::get($data, 'id_photo');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->id_photo = $file_name;
                }
                if (Arr::has($data, 'biography')) {
                    $file = Arr::get($data, 'biography');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->biography = $file_name;
                }
                if (Arr::has($data, 'visa')) {
                    $file = Arr::get($data, 'visa');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->visa = $file_name;
                }
                if (Arr::has($data, 'municipal_card')) {
                    $file = Arr::get($data, 'municipal_card');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->municipal_card = $file_name;
                }
                if (Arr::has($data, 'health_insurance')) {
                    $file = Arr::get($data, 'health_insurance');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->health_insurance = $file_name;
                }
                if (Arr::has($data, 'passport')) {
                    $file = Arr::get($data, 'passport');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->passport = $file_name;
                }
                if (Arr::has($data, 'employee_sponsorship')) {
                    $file = Arr::get($data, 'employee_sponsorship');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->employee_sponsorship = $file_name;
                }
                if (Arr::has($data, 'employee_residence')) {
                    $file = Arr::get($data, 'employee_residence');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->employee_residence = $file_name;
                }

                if (isset($data['end_visa'])) {
                    $user->end_visa = $data['end_visa'];
                }

                if (isset($data['end_passport'])) {
                    $user->end_passport = $data['end_passport'];
                }

                if (isset($data['end_employee_sponsorship'])) {
                    $user->end_employee_sponsorship = $data['end_employee_sponsorship'];
                }

                if (isset($data['end_municipal_card'])) {
                    $user->end_municipal_card = $data['end_municipal_card'];
                }

                if (isset($data['end_health_insurance'])) {
                    $user->end_health_insurance = $data['end_health_insurance'];
                }

                if (isset($data['end_employee_residence'])) {
                    $user->end_employee_residence = $data['end_employee_residence'];
                }
                $user->basic_salary = $data['basic_salary'];
                $user->company_id = auth()->user()->company_id;
                $user->type = UserTypes::HR;
                $user->save();
                Salary::create([
                    'user_id' => $user->id,
                    'salary' => $user->basic_salary,
                    'basic_salary' => $user->basic_salary,
                    'rewards' => 0,
                    'adversaries' => 0,
                    'housing_allowance' => 0,
                    'transportation_allowance' => 0,
                    'company_id' => auth()->user()->company_id,
                    'date' => date('Y-m-d'),
                ]);
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }

            DB::commit();

            if ($user === null) {
                return ['success' => false, 'message' => "User was not created"];
            }

            return ['success' => true, 'data' => $user];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function create_admin($data)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::SUPER_ADMIN) {
                $user = new User();
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = Hash::make($data['password']);
                $user->gender = $data['gender'];
                $user->phone = $data['phone'];
                $user->company_id = $data['company_id'];
                $user->serial_number = $data['serial_number'];
                $user->work_email = $data['work_email'];
                $user->mobile = $data['mobile'];
                $user->nationalitie_id = $data['nationalitie_id'];
                $user->birthday_date = $data['birthday_date'];
                $user->material_status = $data['material_status'];
                $user->address = $data['address'];
                $user->branch = $data['branch'];
                $user->type = UserTypes::ADMIN;

                if (Arr::has($data, 'image')) {
                    $file = Arr::get($data, 'image');
                    $file_name = $this->uploadEmployeeAttachment($file, $user->id);
                    $user->image = $file_name;
                }
                $user->save();
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }

            DB::commit();

            if ($user === null) {
                return ['success' => false, 'message' => "User was not created"];
            }

            return ['success' => true, 'data' => $user];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update_salary($data)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($data['user_id']);
            if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $user->company_id) {
                $user->update([
                    'next_month_salary' => $data['new_salary'],
                ]);
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();
            return ['success' => true, 'data' => $user];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function check_location($data)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($data['user_id']);
            $company = Company::where('id', $user->company_id)->first();
            $company_location = Location::where('company_id', $company->id)->first();
            $longitude_company = floatval($company_location->longitude);
            $latitude_company = floatval($company_location->latitude);
            $longitude_employee = floatval($data['longitude']);
            $latitude_employee = floatval($data['latitude']);
            $deltaLat = deg2rad($latitude_employee - $latitude_company);
            $deltaLon = deg2rad($longitude_employee - $longitude_company);
            $earthRadius = 6371000;
            $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos(deg2rad($latitude_company)) * cos(deg2rad($latitude_employee)) * sin($deltaLon / 2) * sin($deltaLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;
            $threshold = 50;
            $today_date = date("Y-m-d");
            $current_time = Carbon::now();
            $attendance = Attendance::where('date', $today_date)->where('user_id', $data['user_id'])->first();
            if (isset($attendance)) {
                if ($attendance->login_time != null && $attendance->logout_time == null && $distance <= $threshold) {
                    $attendance->touch();
                } elseif ($attendance->login_time != null && $attendance->logout_time == null && $distance > $threshold) {
                    $attendance->update([
                        'logout_time' => $current_time->format('H:i:s')
                    ]);

                    $user = User::findOrFail($data['user_id']);

                    $title = "You Have New Notification";
                    $body = "You are checked out of the company, you have exceeded the company domain";
                    $device_key = User::where('id', $user->id)->pluck('device_key')->first();

                    $notification = new Notification();
                    $notification->user_id = $user->id;
                    $notification->company_id = $user->company_id;
                    $notification->notifier_id = $user->id;
                    $notification->message =  "You are checked out of the company, you have exceeded the company domain";
                    $notification->save();

                    $content = $notification->message;
                    $type = "notification";

                    if ($user->device_key != null) {
                        NotificationService::sendNotification($device_key, $body, $title, $content, $type);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function renewal_employment_contract($data)
    {
        $user = User::findOrFail($data['user_id']);
        $contract = Contract::where('user_id', $data['user_id'])->first();

        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $user->company_id) {
                $date = \Carbon\Carbon::create($user->end_job_contract);

                $newDateThreeMonth = $date->copy()->addMonths(3)->format('Y-m-d');

                $newDateSixMonth = $date->copy()->addMonths(6)->format('Y-m-d');

                $newDateYear = $date->copy()->addMonths(12)->format('Y-m-d');

                if (isset($data['new_date']) && isset($data['number_of_month'])) {
                    return ['success' => false, 'message' => "Please Choose New Date Or Number Of Month Not Together"];
                }

                if (isset($data['new_date'])) {
                    $user->update([
                        'end_job_contract' => $data['new_date'],
                    ]);
                    $contract->update([
                        'end_contract_date' => $data['new_date'],
                    ]);
                } elseif (isset($data['number_of_month']) && $data['number_of_month'] == TerminateTime::THREE_MONTH) {


                    $contract->update([
                        'end_contract_date' => $newDateThreeMonth,
                        'previous_terminate_period' => $data['number_of_month'],
                    ]);
                    $user->update([
                        'end_job_contract' => $newDateThreeMonth,
                    ]);
                } elseif (isset($data['number_of_month']) && $data['number_of_month'] == TerminateTime::SIX_MONTH) {

                    $contract->update([
                        'end_contract_date' => $newDateSixMonth,
                        'previous_terminate_period' => $data['number_of_month'],
                    ]);
                    $user->update([
                        'end_job_contract' => $newDateSixMonth,
                    ]);
                } elseif (isset($data['number_of_month']) && $data['number_of_month'] == TerminateTime::ONE_YEAR) {

                    $contract->update([
                        'end_contract_date' => $newDateYear,
                        'previous_terminate_period' => $data['number_of_month'],
                    ]);
                    $user->update([
                        'end_job_contract' => $newDateYear,
                    ]);
                }
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();
            return ['success' => true, 'data' => $contract->load('user')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function cancle_employees_contract($data)
    {

        $user = User::findOrFail($data['user_id']);
        $contract = Contract::where('user_id', $data['user_id'])->first();

        $hasUnpaidDeposits = $user->deposits()->where('status', DepositStatus::UN_PAID)
            ->orWhere('extra_status', DepositStatus::UN_PAID_REJECTED)
            ->whereHas('user', function ($query) use ($user) {
                $query->where('id', $user->id);
            })->exists();

        DB::beginTransaction();
        try {
            if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::ADMIN && auth()->user()->company_id == $user->company_id) {

                if (!$hasUnpaidDeposits) {

                    $date = \Carbon\Carbon::create($user->end_job_contract);


                    $newDateThreeMonth = $date->copy()->addMonths(3)->format('Y-m-d');
                    $newDateThreeMonthString = \Carbon\Carbon::createFromFormat('Y-m-d', $newDateThreeMonth);


                    $newDateSixMonth = $date->copy()->addMonths(6)->format('Y-m-d');
                    $newDateSixMonthString = \Carbon\Carbon::createFromFormat('Y-m-d', $newDateSixMonth);

                    $newDateYear = $date->copy()->addMonths(12)->format('Y-m-d');
                    $newDateYearString = \Carbon\Carbon::createFromFormat('Y-m-d', $newDateYear);


                    $diffThreeMonths = $newDateThreeMonthString->diff($date)->format('%m months, %d days');
                    $diffSixMonths = $newDateSixMonthString->diff($date)->format('%m months, %d days');
                    $diffYears = $newDateYearString->diff($date)->format('%y years,%m months, %d days');

                    if ($data['terminate_type'] == TerminateType::TEMPORARY) {
                        if (isset($data['contract_termination_period']) && $data['contract_termination_period'] == TerminateTime::THREE_MONTH) {
                            $contract->update([
                                'end_contract_date' => $newDateThreeMonth,
                                "contract_termination_date" => date('Y-m-d'),
                                "contract_termination_period" => $diffThreeMonths,
                                "contract_termination_reason" => $data['contract_termination_reason'],
                            ]);
                        } elseif (isset($data['contract_termination_period']) && $data['contract_termination_period'] == TerminateTime::SIX_MONTH) {
                            $contract->update([
                                'end_contract_date' => $newDateSixMonth,
                                "contract_termination_date" => date('Y-m-d'),
                                "contract_termination_period" => $diffSixMonths,
                                "contract_termination_reason" => $data['contract_termination_reason'],
                            ]);
                        } elseif (isset($data['contract_termination_period']) && $data['contract_termination_period'] == TerminateTime::ONE_YEAR) {
                            $contract->update([
                                'end_contract_date' => $newDateYear,
                                "contract_termination_date" => date('Y-m-d'),
                                "contract_termination_period" => $diffYears,
                                "contract_termination_reason" => $data['contract_termination_reason'],
                            ]);
                        } elseif (isset($data['contract_termination_period']) && $data['contract_termination_period'] == TerminateTime::OPEN_TERM) {
                            $contract->update([
                                'end_contract_date' => date('Y-m-d'),
                                "contract_termination_date" => date('Y-m-d'),
                                "contract_termination_period" => 'Undefined',
                                "contract_termination_reason" => $data['contract_termination_reason'],
                            ]);
                        }
                    } elseif ($data['terminate_type'] == TerminateType::PERMANENT) {
                        $user->update([
                            'status' => EmployeeStatus::DISMISSED,
                        ]);
                        $contract->update([
                            'end_contract_date' => date('Y-m-d'),
                            "contract_termination_date" => date('Y-m-d'),
                            "contract_termination_period" => 'Dismissed',
                            "contract_termination_reason" => 'Dismissed',
                        ]);
                    }
                } else {
                    return ['success' => false, 'message' => "You Cannot Terminate This Employee Because This Employee Have Deposit Unpaided Or UN PAID REJECTED."];
                }
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();
            return ['success' => true, 'data' => $contract->load('user')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function attendance_overview($filter)
    {
        if (auth()->user()->type == UserTypes::HR || auth()->user()->type == UserTypes::ADMIN) {
            $records = Percentage::query()->where('company_id', auth()->user()->company_id);
            if ($filter instanceof AttendanceOverviewFilter) {
                $records->when(isset($filter->year), function ($records) use ($filter) {
                    $records->where('year', 'LIKE', $filter->getYear());
                });
                return ['success' => true, 'data' => $records->get()];
            }
            return ['success' => true, 'data' => $records->get()];
        } else {
            return ['success' => false, 'message' => "Unauthorized"];
        }
    }

    public function check_in_attendance($data)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->user()->id;
            $user = auth()->user();
            $date = date('Y-m-d');
            $existingAttendance = Attendance::where('user_id', $userId)
                ->where('date', $date)
                ->where('login_time', '!=', null)
                ->where('logout_time', '=', null)
                ->first();
            if ($existingAttendance) {
                return ['success' => false, 'message' => 'Attendance already exists'];
            }

            $holidays = Holiday::where('date', $date)
                ->orWhere(function ($query) use ($date) {
                    $query->where('start_date', '<=', $date)
                        ->where('end_date', '>=', $date)
                        ->orWhere('start_date', '=', $date)
                        ->orWhere('end_date', '=', $date)
                        ->orWhere('day_name', date('l'));
                })->get();
            foreach ($holidays as  $holiday) {
                if ($holiday && $holiday->type == HolidayTypes::WEEKLY) {
                    // Today is a weekly holiday, do not allow attendance
                    return ['success' => false, 'message' => 'Today is a weekly holiday'];
                } elseif ($holiday && $holiday->type == HolidayTypes::ANNUL) {
                    // Today is an annual holiday, check if it falls within the start and end dates
                    $startDate = Carbon::parse($holiday->start_date);
                    $endDate = Carbon::parse($holiday->end_date);
                    if ($date >= $startDate && $date <= $endDate) {
                        // Today is an annual holiday, do not allow attendance
                        return ['success' => false, 'message' => 'Today is an annual holiday'];
                    }
                }
            }

            $shifts = auth()->user()->shifts;
            $shiftMatched = false; // variable to keep track of whether any shift matched the current time
            foreach ($shifts as $shift) {
                $start_time = Carbon::parse(date('Y-m-d ') . $shift['start_time'], 'Asia/Riyadh')->timezone('Asia/Riyadh');
                $end_time = Carbon::createFromFormat('H:i:s', $shift['end_time'])->timezone('Asia/Riyadh');
                $current_time = Carbon::now();

                if ($current_time->between($start_time, $end_time)) {
                    $attendance  = new Attendance();
                    $attendance->user_id = $userId;
                    $attendance->date = $date;
                    $attendance->company_id = auth()->user()->company_id;
                    $attendance->status = $data['check_in'];
                    $attendance->login_time = $current_time->format('H:i:s');
                    $attendance->save();

                    $user->update([
                        'status' => EmployeeStatus::ACTIVE,
                    ]);

                    $shiftMatched = true; // set the variable to true if a shift matched the current time
                }
            }

            if (!$shiftMatched) {
                return ['success' => false, 'message' => 'Current time is outside your shifts'];
            }

            DB::commit();

            return ['success' => true, 'data' => $attendance->load('user')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function check_out_attendance($data)
    {
        DB::beginTransaction();

        try {

            $userId = auth()->user()->id;

            $date = date('Y-m-d');

            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $date)
                ->whereNotNull('login_time')
                ->latest('login_time')
                ->first();

            $attendance->update([
                'logout_time' => Carbon::now()->format('H:i:s'),
            ]);
            $user = auth()->user();
            $user->update([
                'status' => EmployeeStatus::ABSENT,
            ]);

            DB::commit();

            return ['success' => true, 'data' => $attendance->load('user')];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function update_working_hours($data)
    {
        DB::beginTransaction();
        try {
            $employeeAvailableTime =  EmployeeAvailableTime::where('user_id', $data['user_id'])->first();
            if (auth()->user()->type == UserTypes::ADMIN || auth()->user()->type == UserTypes::HR && auth()->user()->company_id == $employeeAvailableTime->company_id) {

                if (isset($data['hourly_annual']) && isset($data['daily_annual'])) {

                    $employeeAvailableTime->update([
                        'hourly_annual' => $data['hourly_annual'],
                        'daily_annual' => $data['daily_annual']

                    ]);
                } elseif (isset($data['hourly_annual'])) {

                    $employeeAvailableTime->update([
                        'hourly_annual' => $data['hourly_annual'],
                    ]);
                } elseif (isset($data['daily_annual'])) {
                    $employeeAvailableTime->update([
                        'daily_annual' => $data['daily_annual']
                    ]);
                } else {
                    $employeeAvailableTime->update();
                }
            } else {
                return ['success' => false, 'message' => "Unauthorized"];
            }
            DB::commit();

            if ($employeeAvailableTime === null) {
                return ['success' => false, 'message' => "User was not created"];
            }

            return ['success' => true, 'data' => $employeeAvailableTime];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function reward_adversaries_salary($data)
    {
        $salary = Salary::where('user_id', $data['user_id'])->first();
        if (isset($data['rewards_type']) && $data['rewards_type'] == RewardsType::NUMBER) {
            $salary->update([
                'rewards' => $data['rewards'],
                'salary' =>  $salary->salary + $data['rewards'],
                'rewards_type' => $data['rewards_type'],
                'date' => date('Y-m-d'),
            ]);
        } elseif (isset($data['rewards_type']) && $data['rewards_type'] == RewardsType::RATE) {

            $oldSalary = $salary->salary;
            $rewardPercentage = $data['rewards'];
            $rewardAmount = ($oldSalary * $rewardPercentage) / 100;
            $totalSalary = $oldSalary + $rewardAmount;

            $salary->update([
                'rewards' => $data['rewards'],
                'salary' => $totalSalary,
                'rewards_type' => $data['rewards_type'],
                'date' => date('Y-m-d'),

            ]);
        }
        if (isset($data['adversaries_type']) && $data['adversaries_type'] == AdversariesType::NUMBER) {
            $salary->update([
                'adversaries' => $data['adversaries'],
                'salary' =>  $salary->salary - $data['adversaries'],
                'adversaries_type' => $data['adversaries_type'],
                'date' => date('Y-m-d'),
            ]);
        } elseif (isset($data['adversaries_type']) && $data['adversaries_type'] == AdversariesType::RATE) {
            $oldSalary = $salary->salary;
            $adversariesPercentage = $data['adversaries'];
            $adversariesAmount = ($oldSalary * $adversariesPercentage) / 100;
            $totalSalary = $oldSalary - $adversariesAmount;

            $salary->update([
                'adversaries' => $data['adversaries'],
                'salary' => $totalSalary,
                'adversaries_type' => $data['adversaries_type'],
                'date' => date('Y-m-d'),
            ]);
        }


        return ['success' => true, 'data' => $salary->load('user')];
    }

    public function profile()
    {
        return User::where('id', Auth::id())->with(['salaries', 'availableTime', 'requests', 'attendancesMonthly', 'deposits'])->first();
    }

    public function model()
    {
        return User::class;
    }
}
