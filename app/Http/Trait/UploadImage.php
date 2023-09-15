<?php

namespace App\Http\Trait;

use App\Models\User;
use Illuminate\Support\Str;

trait UploadImage
{
    public function uploadEmployeeAttachment($file, $user_id)
    {
        $filename = date('Y-m-d') . '-Employee-' . $user_id . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('employees'), $filename);
        $path = 'employees/' . $filename;
        return $path;
    }
    public function deleteAndUploadEmployeeAttachment($file, $user_id, $field)
    {
        $oldFilePath = public_path($this->getUserPhotoPath($user_id, $field));

        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        $filename = date('Y-m-d') . '-Employee-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('employees'), $filename);
        $path = 'employees/' . $filename;

        $user = User::find($user_id);
        $user->$field = $filename;
        $user->save();

        return $path;
    }
    public function getUserPhotoPath($user_id, $field)
    {
        $user = User::find($user_id);
        return $user->$field;
    }
    public function uploadCompanyAttachment($file)
    {
        $filename = date('Y-m-d') . '-Company-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('companies'), $filename);
        $path = 'companies/' . $filename;
        return $path;
    }
    public function uploadEmployeeRequestsAttachment($file, $user_id)
    {
        $filename = date('Y-m-d') . '-EmployeeRequests-' . $user_id . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('employees_requests'), $filename);
        $path = 'employees_requests/' . $filename;
        return $path;
    }
    public function uploadEmployeeDepositsAttachment($file, $user_id)
    {
        $filename = date('Y-m-d') . '-EmployeeDeposit-' . $user_id . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('employees_deposits'), $filename);
        $path = 'employees_deposits/' . $filename;
        return $path;
    }
    public function uploadPostsAttachment($file)
    {
        $filename = date('Y-m-d') . '-Post-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('posts'), $filename);
        $path = 'posts/' . $filename;
        return $path;
    }
}
