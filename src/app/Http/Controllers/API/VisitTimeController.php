<?php

namespace App\Http\Controllers\API;

use App\Models\Doctor;
use App\Models\VisitTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitTimeController extends BaseController
{
    public function create_visit_time(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|numeric',
            'month' => 'required|numeric',
            'day' => 'required|numeric',
            'hour' => 'required|string',
            'doctor_name' => 'required|string',
        ],
            [
                'year.required' => 'لطفا سال را وارد نماييد',
                'year.numeric' => 'لطفا سال را به صورت عددی وارد نماييد',
                'month.required' => 'لطفا ماه را وارد نماييد',
                'month.numeric' => 'لطفا ماه را به صورت عددی وارد نماييد',
                'day.required' => 'لطفا روز را وارد نماييد',
                'day.numeric' => 'لطفا روز را به صورت عددی وارد نماييد',
                'hour.required' => 'لطفا ساعت را وارد نماييد',
                'hour.string' => 'لطفا ساعت را به صورت عددی وارد نماييد',
                'doctor_name.required' => 'لطفا نام پزشک را وارد نماييد',
                'doctor_name.string' => 'لطفا نام کامل پزشک را به صورت رشته وارد نماييد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $doctor_id = Doctor::whereName($request['doctor_name'])->first();
        if (is_null($doctor_id))
            return $this->sendError('پزشکی با نام '. $request['doctor_name'] .' یافت نشد.', 'پزشکی با نام '. $request['doctor_name'] .' یافت نشد.');

        $doctor_id = $doctor_id['id'];
        $visit = VisitTime::create([
           'year' => $request['year'],
           'month' => $request['month'],
           'day' => $request['day'],
           'hour' => $request['hour'],
           'doctor_id' => $doctor_id
        ]);

        return $this->sendResponse($visit, 'زمان ویزیت با موفقیت ثبت شد.');
    }
}
