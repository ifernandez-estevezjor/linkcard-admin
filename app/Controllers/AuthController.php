<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CIAuth;
use App\Libraries\Hash;
use App\Models\User;

class AuthController extends BaseController
{
    protected $helpers = ['url', 'form'];
    
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

}
