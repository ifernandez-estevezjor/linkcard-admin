<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CIAuth;
use App\Libraries\Hash;
use App\Models\User;
use App\Models\PasswordResetToken;
use Carbon\Carbon;

class AuthController extends BaseController
{
    protected $helpers = ['url', 'form','CIMail'];
    
    public function loginForm()
    {
        $data = [
            'pageTitle'=>'Login',
            'validation'=>null
        ];
        return view('backend/pages/auth/login', $data);
    }

    public function loginHandler(){
        $fieldType = filter_var($this->request->getVar('login_id'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if ($fieldType == 'email') {
            $isValid = $this->validate([
                'login_id'=>[
                    'rules'=>'required|valid_email|is_not_unique[users.email]',
                    'errors'=>[
                        'required'=>'Escribe tu correo',
                        'valid_email'=>'Por favor, escribe un correo válido',
                        'is_not_unique'=>'El correo no existe'
                    ]
                ],
                'password'=>[
                    'rules'=>'required|max_length[45]|min_length[5]',
                    'errors'=>[
                        'required'=>'Escribe tu contraseña',
                        'min_lenght'=>'La contraseña debe tener al menos 5 caracteres',
                        'max_lenght'=>'La contraseña no debe exceder los 45 caracteres'
                    ]
                ]
            ]);
        }else{
            $isValid = $this->validate([
                'login_id'=>[
                    'rules'=>'required|is_not_unique[users.username]',
                    'errors'=>[
                        'required'=>'Escribe tu nombre de usuario',
                        'is_not_unique'=>'El nombre de usuario no existe'
                    ]
                ],
                'password'=>[
                    'rules'=>'required|max_length[45]|min_length[5]',
                    'errors'=>[
                        'required'=>'Escribe tu contraseña',
                        'min_lenght'=>'La contraseña debe tener al menos 5 caracteres',
                        'max_lenght'=>'La contraseña no debe exceder los 45 caracteres'
                    ]
                ]
            ]);
        }
        if(!$isValid){
            return view('backend/pages/auth/login',[
                'pageTitle'=>'Login',
                'validation'=>$this->validator
            ]);
        }else{
            $user = new User();
            $userInfo = $user->where($fieldType, $this->request->getVar('login_id'))->first();
            $check_password = Hash::check($this->request->getVar('password'), $userInfo['password']);

            if (!$check_password) {
                return redirect()->route('admin.login.form')->with('fail', 'Contraseña incorrecta')->withInput();
            }else{
                CIAuth::setCIAuth($userInfo);
                return redirect()->route('admin.home');
            }
        }
    }

    public function forgotForm(){
        $data = array(
            'pageTitle'=>'Forgot Password',
            'validation'=>null
        );
        return view('backend/pages/auth/forgot', $data);
    }

    public function sendPasswordResetLink(){
        $isValid = $this->validate([
            'email'=>[
                'rules'=>'required|valid_email|is_not_unique[users.email]',
                'errors'=>[
                    'required'=>'Escribe tu correo',
                    'valid_email'=>'Por favor, escribe un correo válido',
                    'is_not_unique'=>'El correo no existe en el sistema'
                ],
            ]
        ]);

        if (!$isValid) {
            return view('backend/pages/auth/forgot',[
                'pageTitle'=>'Contraseña Olvidada',
                'validation'=>$this->validator
            ]);
        }else{
            //Obtener detalles del usuario admin
            $user = new User();
            $user_info = $user->asObject()->where('email',$this->request->getVar('email'))->first();

            //Generar token
            $token = bin2hex(openssl_random_pseudo_bytes(65));

            //Obtener token para resetear password
            $password_reset_token = new PasswordResetToken();
            $isOldTokenExists = $password_reset_token->asObject()->where('email',$user_info->email)->first();

            if ($isOldTokenExists) {
                //Actualizar token existente
                $password_reset_token->where('email', $user_info->email)->set(['token'=>$token,'created_at'=>Carbon::now()])->update();
            }else{
                $password_reset_token->insert([
                    'email'=>$user_info->email,
                    'token'=>$token,
                    'created_at'=>Carbon::now()
                ]);
            }
            // Crear enlace de accion
            //$actionLink = route_to('admin.reset-password', $token);
            $actionLink = base_url(route_to('admin.reset-password', $token));

            $mail_data = array(
                'actionLink'=>$actionLink,
                'user'=>$user_info
            );
            $view = \Config\Services::renderer();
            $mail_body = $view->setVar('mail_data', $mail_data)->render('email-templates/forgot-email-template');

            $mailConfig = array(
                'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                'mail_from_name'=>env('EMAIL_FROM_NAME'),
                'mail_recipient_email'=>$user_info->email,
                'mail_recipient_name'=>$user_info->name,
                'mail_subject'=>'Resetear contrasena',
                'mail_body'=>$mail_body
            );

            // Enviar email
            if(sendEmail($mailConfig)){
                return redirect()->route('admin.forgot.form')->with('success','Se ha enviado un correo para resetear tu contraseña');
            }else{
                return redirect()->route('admin.forgot.form')->with('fail','Algo salió mal');
            }
        }
    }

    public function resetPassword($token){
        $passwordResetPassword = new PasswordResetToken();
        $check_token = $passwordResetPassword->asObject()->where('token', $token)->first();

        if(!$check_token){
            return redirect()->route('admin.forgot.form')->with('fail','Token inválido. Realiza otra solicitud para recueperar tu contraseña');
        }else {
            //Verifica si el token no ha expirado
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s', $check_token->created_at)->diffInMinutes(Carbon::now());

            if($diffMins > 15){
                //Si el token expiró
                return redirect()->route('admin.forgot.form')->with('fail','Token expirado. Realiza otra solicitud para recueperar tu contraseña');
            }else {
                return view('backend/pages/auth/reset',[
                    'pagetitle' => 'Resetear Contrasena',
                    'validation'=>null,
                    'token'=>$token
                ]);
            }
        }
    }

    public function resetPasswordHandler($token){
        $isValid = $this->validate([
            'new_password'=>[
                'rules'=>'required|max_length[20]|min_length[5]|is_password_strong[new_password]',
                'errors'=>[
                    'required'=>'Escribe la nueva contrasena',
                    'min_lenght'=>'La nueva contrasena debe tener por lo msenos 5 caracteres',
                    'max_lenght'=>'La nueva contrasena debe tener mámínimo 20 caracters',
                    'is_password_strong'=>'La nueva contrasena debe tener 1 letra mayúscula, 1 letra minúscula, 1 número y 1 caracter especial'
                ]
            ],
            'confirm_new_password'=>[
                'rules'=>'required|matches[new_password]',
                'errors'=>[
                    'required'=>'Confirmar nueva contrasena',
                    'matches'=>'La contrasena no es la misma.'
                ]
            ]
        ]);

        if (!$isValid) {
            return view('backend/pages/auth/reset',[
                'pageTitle'=>"Reseteart contrasena",
                'validation'=>null,
                'token'=>$token
            ]);
        }else {
            // Obtener detalles del token
            $passwordResetPassword = new PasswordResetToken();
            $get_token = $passwordResetPassword->asObject()->where('token', $token)->first();

            // Obtener detalles del usuario (admin)
            $user = new User();
            $user_info = $user->asObject()->where('email', $get_token->email)->first();

            if(!$get_token){
                return redirect()->back()->with('fail','Token inválido')->withInput();
            }else{
                //Actualizar la contraseña del admin
                $user->where('email', $user_info->email)->set(['password'=>Hash::make($this->request->getVar('new_password'))])->update();

                //Enviar notificación al correo del usuario (admin)
                $mail_data = array(
                    'user'=>$user_info,
                    'new_password'=>$this->request->getVar('new_password')
                );

                $view = \Config\Services::renderer();
                $mail_body = $view->setVar('mail_data', $mail_data)->render('email-templates/password-changed-email-template');

                $mailConfig = array(
                    'mail_from_email'=>env('EMAIL_FROM_ADDRESS'),
                    'mail_from_name'=>env('EMAIL_FROM_NAME'),
                    'mail_recipient_email'=>$user_info->email,
                    'mail_recipient_name'=>$user_info->name,
                    'mail_subject'=>'Cambiar Contrasena',
                    'mail_body'=>$mail_body
                );

                if(sendEmail($mailConfig)){
                    //Eliminar token
                    $passwordResetPassword->where('email', $user_info->email)->delete();

                    //Redirigir y mostrar el mensaje en el login
                    return redirect()->route('admin.login.form')->with('success', '¡Confirmado! Tu contrasena se ha cambiado, puedes iniciar sesión');
                }else {
                    return redirect()->back()->with('fail','Algo salió mal')->withInput();
                }
            }
        }
    }

}
