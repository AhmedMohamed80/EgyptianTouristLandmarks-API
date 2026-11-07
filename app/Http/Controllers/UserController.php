<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\user;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{

public function testToken(Request $request)
{

    return response()->json([
        'header_token' => $request->header('Authorization')
    ]);


}

    public function _construct(){
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request){
        try{
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'email'=> 'required|string|email|unique:users',
            'phone'=> 'required',
            'password'=> 'required|string|confirmed|min:6'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'faild', 
                    'errors' => $validator->errors()   
                ], 400);
            }
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
            ]);
            if (!$token = auth('api')->attempt($validator->validated())) {
                return response()->json([
                    'message' => 'failed'
                ], 401);
            }
            return response()->json([
            'message'=> 'successfull',
            'user'=>$user,
            $this->createNewToken($token)

            ],201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'faild :' . $e->getMessage()], 500);
        }
        }   
        public function login(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6'
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'failed',
                    'errors' => $validator->errors()
                ], 400);
            }
        
            if (!$token = auth('api')->attempt($validator->validated())) {
                return response()->json([
                    'message' => 'failed'
                ], 401);
            }
        
            return $this->createNewToken($token);
        }
        
        // دالة إنشاء التوكن وإرجاع بيانات المستخدم
        public function createNewToken($token){
            return response()->json([
                'message' => 'successful',
                'access_token' => $token,
                'user' => auth('api')->user()
            ]);
        }
        
        public function profile()
        {
            try {
                $payload = JWTAuth::parseToken()->getPayload();
                return response()->json(['payload' => $payload]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 401);
            }
        }
        

        public function logout(){
            auth('api')->invalidate(true);
            return response()->json(['message' => 'successful']);
        }
    
        public function uploadProfileImage(Request $request)
        {
            try {
                if (!$request->hasFile('profile_image')) {
                    return response()->json(['error' => 'No profile image uploaded.'], 400);
                }
        
                $validator = Validator::make($request->all(), [
                    'profile_image' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
                ]);
        
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 400);
                }
        
                $token = JWTAuth::getToken();
                if (!$token) {
                    return response()->json(['error' => 'Unauthorized - Token not found'], 401);
                }
        
                $payload = JWTAuth::parseToken()->getPayload();
                $userEmail = $payload->get('email');
        
                // البحث عن المستخدم باستخدام البريد الإلكتروني
                $user = User::where('email', $userEmail)->first();
        
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }
        
                // تحميل الصورة
                $file = $request->file('profile_image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $path = 'image/users/';
                $file->move(public_path($path), $fileName);
        
                // تحديث الصورة في قاعدة البيانات
                $user->profile_image = $path . $fileName;
                $user->save();
        
                return response()->json([
                    'message' => 'Success',
                    'profile_image' => asset($path . $fileName)
                ], 200);
        
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
            }
        }
        public function updatePassword(Request $request)
        {
            try{
                $request->validate([
                    'current_password' => 'required',
                    'new_password' => 'required|min:6|confirmed',
                ]);
    
                $user = Auth::user(); // الحصول على المستخدم المسجل حاليًا
    
                // يسشوف كلمة المرور الحالية صح ولا لا
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json(['message' => 'current password un correct'], 400);
                }
    
                // تحديث كلمة المرور
                $user->password = Hash::make($request->new_password);
                $user->save();
    
                return response()->json(['message' => 'successful']);


            } catch (\Exception $e) {
                return response()->json(['error' => 'faild :' . $e->getMessage()], 500);
            }
        }
        
}
