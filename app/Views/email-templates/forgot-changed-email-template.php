<p>Estimado <b><?= $mail_data['user']->name ?></b></p>
<br>
<p>
    Tu contrase&ntilde;a en nuestro sitio www.linkcard.com.mx se cambió correctamente. Estos son tus datos para iniciar sesión:
    <br><br>
    <b>Login ID: </b><?= $mail_data['user']->username ?> o <?= $mail_data['user']->email ?>
    <br>
    <b>Contrase&ntilde;a: </b> <?= $mail_data['new_password'] ?>
</p>
<br><br>
Por favor, guarda tus datos en un lugar seguro. Tu nombre de usuario y contrase&ntilde;a son tuyos y no debes compartirlos con nadie.
<p>Linkcard no har&aacute; mal uso de tu nombre de usuario o contrase&ntilde;a</p>
<br>
----------------------------------------------------------------------------------
<p>Este correo fue enviado autom&aacute;ticamente por el sistema de www.linkcard.com.mx. No responder.</p>