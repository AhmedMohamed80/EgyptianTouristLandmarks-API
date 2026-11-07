<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    
    public function sendResetCode(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
    
        // توليد كود عشوائي مكون من 6 أرقام
        $code = rand(100000, 999999);
        $email = $request->email; 
    
        // جلب اسم المستخدم من قاعدة البيانات
        $user = User::where('email', $email)->first();
    
        // حفظ الكود في قاعدة البيانات مع تحديثه إذا كان موجودًا مسبقًا
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => (string) $code, 'created_at' => Carbon::now()]
        );
    
        // إرسال الكود عبر البريد الإلكتروني
        try {
            Mail::send('emails.passwordReset', [
                'name' => $user->name, // تمرير اسم المستخدم إلى البريد الإلكتروني
                'code' => $code
            ], function ($message) use ($email) {
                $message->to($email); 
                $message->subject('your password reset code ');
            });
    
            // رسالة النجاح
            return response()->json([
                'message' => 'successful'
            ]);
    
        } catch (\Exception $e) {
            // رسالة الخطأ إذا فشل إرسال البريد
            return response()->json(['error' => 'faild :' . $e->getMessage()], 500);
        }
    }
    

    
    //التحقق من كود إعادة تعيين كلمة المرور
    public function verifyResetCode(Request $request)
    {
        //بيتاكد من الداتا الي بتدخله 
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
        ]);
        
        //بيشوف الكود موجود في الداتا بيز ولا لا 
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', (string) $request->code) 
            ->first();
    
        if (!$reset) {
            //لو الكود غير موجود أو غلط
            return response()->json(['message' => 'faild'], 400);
        }
    
        // لو الكود صحيح وموجود في قاعدة الداتا بيز
        return response()->json(['message' => 'success']);
    }
    

    
    public function resetPassword(Request $request)
    {   
        //بيتاكد من الداتا الي بتدخله 
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        //بيشوف الكود واليميل موجود في الداتا بيز ولا لا 
        $reset = DB::table('password_reset_tokens')
    ->where('email', $request->email)
    ->where('created_at', '>=', Carbon::now()->subMinutes(10))
    ->first();


        if (!$reset) {
            //لو الداتا غير موجود أو غلط
            return response()->json(['message' => 'faild'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'success']);
    }
}
