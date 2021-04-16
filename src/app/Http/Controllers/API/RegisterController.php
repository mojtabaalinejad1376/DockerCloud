<?php

namespace App\Http\Controllers\API;

use App\Models\Comment;
use App\Models\Doctor;
use App\Models\Favourite;
use App\Models\User;
use App\Models\VisitTime;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|numeric|min:11|unique:App\Models\User',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ],
            [
                'first_name.required' => 'لطفا نام را وارد نماييد',
                'first_name.string' => 'لطفا نام را به صورت رشته وارد نماييد',
                'last_name.required' => 'لطفا نام خانوادگی را وارد نماييد',
                'last_name.string' => 'لطفا نام خانوادگی را به صورت رشته وارد نماييد',
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
                'phone.unique' => 'شماره تلفن قبلا ثبت شده است',
                'password.required' => 'لطفا رمز عبور را وارد نماييد',
                'password.min' => 'رمز عبور نبايد كمتر از 8 كاراكتر باشد',
                'confirm_password.required' => 'لطفا تكرار رمز عبور را وارد نماييد',
                'confirm_password.same' => 'رمز عبور مطابقت ندارد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }
        $register = $request->all();
        $register['password'] = bcrypt($register['password']);
        $user = User::create($register);
        $success['token'] =  $user->createToken('register')->accessToken;
        $success['name'] =  $user->first_name.' '.$user->last_name;

        return $this->sendResponse($success, 'کاربر '. $success['name'] .' ثبت شد');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ],
            [
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'password.required' => 'لطفا رمز عبور را وارد نماييد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        if(Auth::attempt(['phone' => $request['phone'], 'password' => $request['password']])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('register')->accessToken;
            $success['name'] =  $user['first_name'].' '.$user['last_name'];
            return $this->sendResponse($success, 'ورود با موفقیت انجام شد');
        }
        else {
            return $this->sendError('کاربر یافت نشد', ['error' => 'کاربر یافت نشد']);
        }
    }

    public function changeProfile(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if(isset($user)) {
            $id = $user['id'];
            if (isset($request->first_name))
                $user['first_name'] = $request->first_name;
            if (isset($request->last_name))
                $user['last_name'] = $request->last_name;
            if (isset($request->new_phone))
                $user['phone'] = $request->new_phone;
            $edit = User::whereId($id)->update([
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'phone' => $user['phone']
            ]);
            return $this->sendResponse($user, 'ویرایش با موفقیت انجام شد');
        }
        else {
            return $this->sendError('کاربر یافت نشد', ['error' => 'کاربر یافت نشد']);
        }
    }

    public function changePassword(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if(isset($user)) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:8',
                'confirm_password' => 'required|same:password'
            ],
                [
                    'password.required' => 'لطفا رمز عبور جدید را وارد نماييد',
                    'password.min' => 'رمز عبور نبايد كمتر از 8 كاراكتر باشد',
                    'confirm_password.required' => 'لطفا تكرار رمز عبور را وارد نماييد',
                    'confirm_password.same' => 'رمز عبور مطابقت ندارد',
                ]);

            if($validator->fails()){
                return $this->sendError('خطا اعتبارسنجی', $validator->errors());
            }

            $user = User::whereId($user['id'])->update([
                'password' => bcrypt($request['password']),
            ]);
            return $this->sendResponse($user, 'تغییر رمز عبور با موفقیت انجام شد');
        }
        else {
            return $this->sendError('کاربر یافت نشد', ['error' => 'کاربر یافت نشد']);
        }
    }

    public function filter(Request $request)
    {
        if (isset($request['name']))
        {
            $doctor = Doctor::where('name','LIKE','%'.$request['name'].'%')->first();
            if (isset($doctor))
                return $this->sendResponse($doctor, 'دکتر '. $doctor['name'] .' یافت شد.');
            else
                return $this->sendError('پزشکی با نام '. $request['name'] .' یافت نشد.', 'پزشکی با نام '. $request['name'] .' یافت نشد.');
        }
        elseif (isset($request['nezam_number']))
        {
            $doctor = Doctor::where('nezam_number', $request['nezam_number'])->first();
            if (isset($doctor))
                return $this->sendResponse($doctor, 'دکتر '. $doctor['name'] .' با شماره نظام '. $request['nezam_number'] .' یافت شد.');
            else
                return $this->sendError('پزشکی با شماره نظام '. $request['nezam_number'] .' یافت نشد.', 'پزشکی با شماره نظام '. $request['nezam_number'] .' یافت نشد.');
        }
        elseif (isset($request['city']))
        {
            $doctor = Doctor::whereCity($request['city'])->get();
            if (isset($doctor))
                return $this->sendResponse($doctor, 'پزشک در شهر '. $request['city'] .' یافت شد.');
            else
                return $this->sendError('پزشکی در شهر '. $request['city'] .' یافت نشد.', 'پزشکی در شهر '. $request['city'] .' یافت نشد.');
        }
        elseif (isset($request['speciality']))
        {
            $doctor = Doctor::where('speciality','LIKE','%'.$request['speciality'].'%')->get();
            if (isset($doctor))
                return $this->sendResponse($doctor, 'پزشک با تخصص کاری '. $request['speciality'] .' یافت شد.');
            else
                return $this->sendError('پزشکی با تخصص کاری '. $request['speciality'] .' یافت نشد.', 'پزشکی با تخصص کاری '. $request['speciality'] .' یافت نشد.');
        }
        elseif (isset($request['degree']))
        {
            $doctor = Doctor::where('degree','LIKE','%'.$request['degree'].'%')->get();
            if (isset($doctor))
                return $this->sendResponse($doctor, 'پزشک با مدرک تحصیلی '. $request['degree'] .' یافت شد.');
            else
                return $this->sendError('پزشکی با مدرک تحصیلی '. $request['degree'] .' یافت نشد.', 'پزشکی با مدرک تحصیلی '. $request['degree'] .' یافت نشد.');
        }
    }

    public function request_visit_time(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|min:11',
            'visit_id' => 'required|numeric'
        ],
            [
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
                'visit_id.required' => 'لطفا ایدی زمان ویزیت را وارد نماييد',
                'visit_id.numeric' => 'لطفا آیدی زمان ویزیت را به صورت عددی وارد نماييد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $user_id = User::wherePhone($request['phone'])->first();
        if (is_null($user_id))
            return $this->sendError('کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.', 'کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.');
        $visit_flag = VisitTime::whereId($request['visit_id'])->first();
        if (is_null($visit_flag))
            return $this->sendError('زمان ویزیتی با آیدی '. $request['visit_id'] .' یافت نشد.', 'زمان ویزیتی با آیدی '. $request['visit_id'] .' یافت نشد.');
        if ($visit_flag['visit'] == '0')
        {
            $time_request = \App\Models\Request::create([
                'user_id' => $user_id['id'],
                'visit_id' => $request['visit_id']
            ]);
            $visit = VisitTime::whereId($request['visit_id'])->update([
               'visit' => '1'
            ]);
            return $this->sendResponse('زمان ویزیت ثبت شد.', 'زمان ویزیت در تاریخ '. $visit_flag['year'] .'/'. $visit_flag['month'] .'/'. $visit_flag['day'] .' ساعت '. $visit_flag['hour'] .' برای کاربر '. $user_id['first_name'] .' '. $user_id['last_name'] .' ثبت شد.');
        }
        else
            return $this->sendError('زمان ویزیت با آیدی '. $request['visit_id'] .' قبلا رزرو شده است.', 'زمان ویزیت با آیدی '. $request['visit_id'] .' قبلا رزرو شده است.');
    }

    public function show_request_visit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|min:11',
        ],
            [
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $user_id = User::wherePhone($request['phone'])->first();
        if (is_null($user_id))
            return $this->sendError('کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.', 'کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.');

        $show = DB::table('requests')
            ->join('users', 'requests.user_id', '=', 'users.id')
            ->join('visit_time', 'requests.visit_id', '=', 'visit_time.id')
            ->join('doctors', 'visit_time.doctor_id', '=', 'doctors.id')
            ->where('requests.user_id', '=', $user_id['id'])
            ->select('users.first_name', 'users.last_name', 'visit_time.year', 'visit_time.month', 'visit_time.day', 'visit_time.hour', 'doctors.name as doctor_name')
            ->get();
        return $this->sendResponse($show, 'زمان های ویزیت رزرو شده برای کاربر '.$user_id['first_name'] .' '. $user_id['last_name'] .' یافت شد.');
    }

    public function favourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_name' => 'required|string',
            'phone' => 'required|numeric|min:11',
        ],
            [
                'doctor_name.required' => 'لطفا نام پزشک را وارد نماييد',
                'doctor_name.string' => 'لطفا نام کامل پزشک را به صورت رشته وارد نماييد',
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $user_id = User::wherePhone($request['phone'])->first();
        if (is_null($user_id))
            return $this->sendError('کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.', 'کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.');

        $doctor_id = Doctor::whereName($request['doctor_name'])->first();
        if (is_null($doctor_id))
            return $this->sendError('پزشکی با نام '. $request['doctor_name'] .' یافت نشد.', 'پزشکی با نام '. $request['doctor_name'] .' یافت نشد.');

        $flag = Favourite::where('user_id', $user_id['id'])->Where('doctor_id', $doctor_id['id'])->first();

        if (isset($flag))
            return $this->sendError('قبلا به لیست پزشکان مورد علاقه اضافه شده است.', 'قبلا به لیست پزشکان مورد علاقه اضافه شده است.');
        else
        {
            Favourite::create([
                'user_id' => $user_id['id'],
                'doctor_id' => $doctor_id['id']
            ]);
            return $this->sendResponse('دکتر '. $doctor_id['name'] .' به پزشکان مورد علاقه کاربر '. $user_id['first_name'] .' '. $user_id['last_name'] .' با موفقیت اضافه شد', 'دکتر '. $doctor_id['name'] .' به پزشکان مورد علاقه کاربر '. $user_id['first_name'] .' '. $user_id['last_name'] .' با موفقیت اضافه شد');
        }
    }

    public function show_favourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|min:11',
        ],
            [
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $user_id = User::wherePhone($request['phone'])->first();
        if (is_null($user_id))
            return $this->sendError('کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.', 'کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.');

        $favourite = DB::table('favourites')
            ->join('users', 'favourites.user_id', '=', 'users.id')
            ->join('doctors', 'favourites.doctor_id', '=', 'doctors.id')
            ->where('favourites.user_id', '=', $user_id['id'])
            ->select('users.first_name as First Name', 'users.last_name as Last Name', 'doctors.name as Doctor Name')
            ->get();
        return $this->sendResponse($favourite, 'پزشکان مورد علاقه برای کاربر '.$user_id['first_name'] .' '. $user_id['last_name'] .' یافت شد.');
    }

    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_name' => 'required|string',
            'phone' => 'required|numeric|min:11',
            'description' => 'required|string'
        ],
            [
                'doctor_name.required' => 'لطفا نام پزشک را وارد نماييد',
                'doctor_name.string' => 'لطفا نام کامل پزشک را به صورت رشته وارد نماييد',
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
                'description.required' => 'لطفا متن نظر خود را وارد نماييد',
                'description.string' => 'لطفا متن نظر خود را به صورت رشته وارد نماييد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $user_id = User::wherePhone($request['phone'])->first();
        if (is_null($user_id))
            return $this->sendError('کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.', 'کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.');

        $doctor_id = Doctor::whereName($request['doctor_name'])->first();
        if (is_null($doctor_id))
            return $this->sendError('پزشکی با نام '. $request['doctor_name'] .' یافت نشد.', 'پزشکی با نام '. $request['doctor_name'] .' یافت نشد.');

        Comment::create([
           'user_id' => $user_id['id'],
           'doctor_id' => $doctor_id['id'],
           'description' => $request['description']
        ]);
        return $this->sendResponse('نظر شما با موفقیت ثبت شد.', 'نظر کاربر '.$user_id['first_name'] .' '. $user_id['last_name'] .' برای دکتر '. $doctor_id['name'] .' با موفقیت اضافه شد.');
    }

    public function show_doctor_comment(Request $request)
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

        $comment = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('doctors', 'comments.doctor_id', '=', 'doctors.id')
            ->where('comments.doctor_id', '=', $doctor_id['id'])
            ->select('users.first_name as User First Name', 'users.last_name as User Last Name', 'doctors.name as Doctor Name', 'comments.description')
            ->get();
        return $this->sendResponse($comment, 'نظرات کاربران برای دکتر '. $doctor_id['name'] .' یافت شد.');
    }

    public function show_user_comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric|min:11',
        ],
            [
                'phone.required' => 'لطفا شماره تلفن را وارد نماييد',
                'phone.numeric' => 'لطفا شماره تلفن را به صورت عددی وارد نماييد',
                'phone.min' => 'شماره تلفن نباید کمتر از 10 رقم باشد',
            ]);

        if ($validator->fails()) {
            return $this->sendError('خطا اعتبارسنجی', $validator->errors());
        }

        $user_id = User::wherePhone($request['phone'])->first();
        if (is_null($user_id))
            return $this->sendError('کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.', 'کاربری با شماره تلفن '. $request['phone'] .' یافت نشد.');

        $comment = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('doctors', 'comments.doctor_id', '=', 'doctors.id')
            ->where('comments.user_id', '=', $user_id['id'])
            ->select('users.first_name as User First Name', 'users.last_name as User Last Name', 'doctors.name as Doctor Name', 'comments.description')
            ->get();
        return $this->sendResponse($comment, 'نظرات داده شده کاربر با نام '. $user_id['first_name'] .' '. $user_id['last_name'] .' برای پزشکان یافت شد.');
    }
}
