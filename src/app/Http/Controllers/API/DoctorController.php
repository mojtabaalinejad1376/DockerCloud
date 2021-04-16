<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DoctorController extends BaseController
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'nezam_number' => 'required|numeric|unique:App\Models\Doctor',
            'city' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|numeric',
            'speciality' => 'required|string',
            'degree' => 'required|string'
        ],
            [
                'name.required' => 'لطفا نام را وارد نماييد',
                'name.string' => 'لطفا نام را به صورت رشته وارد نماييد',
                'nezam_number.required' => 'لطفا شماره نظام را وارد نماييد',
                'nezam_number.numeric' => 'لطفا شماره نظام را به صورت عددی وارد نماييد',
                'nezam_number.unique' => 'شماره نظام قبلا ثبت شده است',
                'city.required' => 'لطفا شهر را وارد نماييد',
                'city.string' => 'لطفا شهر را به صورت رشته وارد نماييد',
                'address.required' => 'لطفا آدرس را وارد نماييد',
                'address.string' => 'لطفا آدرس را به صورت رشته وارد نماييد',
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'speciality.required' => 'لطفا تخصص کاری را وارد نماييد',
                'speciality.string' => 'لطفا تحصص کاری را به صورت رشته وارد نماييد',
                'degree.required' => 'لطفا مدرک تحصیلی را وارد نماييد',
                'degree.string' => 'لطفا مدرک تحصیلی را به صورت رشته وارد نماييد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $doctor = Doctor::create($request->all());
        return $this->sendResponse($doctor, 'دکتر '. $doctor['name'] .' ثبت شد');
    }

    public function show_visit_time(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_name' => 'required|string',
        ],
        [
            'doctor_name.required' => 'لطفا نام پزشک را وارد نماييد',
            'doctor_name.string' => 'لطفا نام کامل پزشک را به صورت رشته وارد نماييد',
        ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $doctor_id = Doctor::whereName($request['doctor_name'])->first();
        if (is_null($doctor_id))
            return $this->sendError('پزشکی با نام '. $request['doctor_name'] .' یافت نشد.', 'پزشکی با نام '. $request['doctor_name'] .' یافت نشد.');

        $visit = DB::table('doctors')
            ->join('visit_time', 'doctors.id', '=', 'visit_time.doctor_id')
            ->select('visit_time.id', 'doctors.name', 'visit_time.year', 'visit_time.month', 'visit_time.day', 'visit_time.hour', 'visit_time.visit')
            ->get();
        return $this->sendResponse($visit, 'زمان های ویزیت برای پزشک '. $request['doctor_name'] .' یافت شد.');
    }
}
