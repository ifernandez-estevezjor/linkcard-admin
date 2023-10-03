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
            $actionLink = route_to('admin.reset-password', $token);

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
                'mail_subject'=>'Resetear contraseña',
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

}
