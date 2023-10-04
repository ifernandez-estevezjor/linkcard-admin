<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CIAuth;
use App\Models\User;
use App\Libraries\Hash;

class AdminController extends BaseController
{
    protected $helpers = ['url','form','CIMail','CIFunctions'];

    public function index()
    {
        $data = [
            'pageTitle'=>'Dashboard'
        ];
        return view('backend/pages/home', $data);
    }

    public function logoutHandler(){
        CIAuth::forget();
        return redirect()->route('admin.login.form')->with('fail', 'Cerraste sesión');
    }

    public function profile(){
        $data = array(
            'pageTitle'=>'Profile'
        );
        return view('backend/pages/profile', $data);
    }

    public function updatePersonalDetails(){
        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $user_id = CIAuth::id();

        if($request->isAJAX()){
            $this->validate([
                'name'=>[
                    'rules'=>'required',
                    'errors'=>[
                        'required'=>'El campo Nombre Completo es requerido'
                    ]
                ],
                'username'=>[
                    'rules'=>'requires|mim_lenght[4]|is_unique[users.username.id,'.$user_id.']',
                    'errors'=>[
                        'required'=>'El campo Nombre de Usuario es requerido',
                        'min_lenght'=>'El Nombre de Usuario debe tener al menos 4 caracteres',
                        'is_unique'=>'El Nombre de Usuario ya existe'
                    ]
                ]
            ]);

            if($validation->run() == FALSE){
                $errors = $validation->getErrors();
                return json_encode(['status'=>0,'error'=>$errors]);
            }else{
                $user = new User();
                $update = $user->where('id',$user_id)->set(['name'=>$request->getVar('name'),'username'=>$request->getVar('username'),'bio'=>$request->getVar('bio')])->update();

                if($update){
                    $user_info = $user->find($user_id);
                    return json_encode(['status'=>1,'user_info'=>$user_info,'msg'=>'¡Tus datos generales han sido actualizados correctamente!']);
                }else {
                    return json_encode(['status'=>0,'msg'=>'Hubo un error, intenta de nuevo']);
                }
            }
        }
    }

    public function updateProfilePicture(){
        $request = \Config\Services::request();
        $user_id = CIAuth::id();
        $user = new User();
        $user_info = $user->asObject()->where('id',$user_id)->first();

        $path = 'images/users/';
        $file = $request->getFile('user_profile_file');
        $old_picture = $user_info->picture;
        $new_filename = 'UIMG'.$user_id.$file->getRandomName();

        /*if($file->move($path,$new_filename)){
            if($old_picture != null && file_exists($path.$old_picture)){
                unlink($path.$old_picture);
            }
            $user->where('id',$user_info->id)->set((['picture'=>$new_filename]))->update();

            echo json_encode(['status'=>1,'msg'=>'¡Hecho! Tu foto de perfil ha sido actualizada correctamente.']);
        }else{
            echo json_encode(['status'=>0,'msg'=>'Hubo un error. Por favor, intenta de nuevo.']);
        }*/

        $upload_image = \Config\Services::image()->withFile($file)->resize(450,450,true,'height')->save($path.$new_filename);

        if ($upload_image) {
            if ($old_picture != null && file_exists($path.$new_filename)) {
                unlink($path.$old_picture);
            }
            $user->where('id',$user_info->id)->set(['picture'=>$new_filename])->update();

            echo json_encode(['status'=>1,'msg'=>'¡Hecho! Tu foto de perfil ha sido actualizada correctamente.']);
        }else{
            echo json_encode(['status'=>0,'msg'=>'Algo salió mal. Por favor, intenta de nuevo.']);
        }
    }

    public function changePassword(){
        $request = \Config\Services::request();

        if ($request->isAJAX()) {
            $validation = \Config\Services::validation();
            $user_id = CIAuth::id();
            $user = new User();
            $user_info = $user->asObject()->where('id',$user_id)->first();

            $this->validate([
                'current_password'=>[
                    'rules'=>'required|min_lenght[5]|check_current_password[current_password]',
                    'errors'=>[
                        'required'=>'Escribe tu contraseña actual',
                        'min_lenght'=>'La contraseña debe tener al menos 5 caracteres',
                        'check_current_password'=>'La contraseña actual es incorrecta'
                    ]
                ],
                'new_password'=>[
                    'rules'=>'required|min_lenght[5]|max_lenght[20]|is_password_strong[new_password]',
                    'errors'=>[
                        'required'=>'Escribe tu contraseña nueva',
                        'min_lenght'=>'La contraseña debe tener al menos 5 caracteres',
                        'max_lenght'=>'La contraseña no debe exceder los 20 caracteres',
                        'is_password_strong'=>'La contraseña debe contener al menos 1 letra mayúscula, 1 letra minúscula, 1 número y 1 caracter especial.'
                    ],
                ],
                'confirm_new_password'=>[
                    'rules'=>'required|matches[new_password]',
                    'errors'=>[
                        'required'=>'Confirma tu nueva contraseña',
                        'matches'=>'La contraseña no corresponde'
                    ]
                ]
            ]);

            if ($validation->run() === FALSE) {
                $errors = $validation->getErrors();
                return $this->response->setJSON(['status'=>0,'token'=>csrf_hash(),'error'=>$errors]);
            }else{
                //$user->where('id',$user_info->id)->set(['password'=>Hash::make($request->getVar('new_password'))])->update(); //Revisar el error en el método make

                //Enviar correo al usuario
                $mail_data = array(
                    'user'=>$user_info,
                    'new_password'=>$request->getVar('new_password')
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

                sendEmail($mailConfig);
                return $this->response->setJSON(['status'=>1,'token'=>csrf_hash(),'msg'=>'¡Hecho! Tu contraseña se actualizó correctamente.']);
            }
        }
    }

    public function settings(){
        $data = [
            'Pagetitle' => 'Settings'
        ];
        return view('backend/pages/settings',$data);
    }

}
