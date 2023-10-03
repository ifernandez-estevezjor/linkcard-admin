<p>Estimado <?= $mail_data['user']->name ?></p>
<p>
    Hemos recibido una solicitud para resetear tu contraseña de nuestro sitio www.linkcard.com.mx
    con la cuenta asociada <i><?= $mail_data['user']->email ?></i>
    Puedes resetear tu contraseña haciendo click en el siguiente botón:
    <br><br>
    <a href="<?= $mail_data['actionLink'] ?>" style="color:#fff;border-color:#22bc66;border-style:solid;
    box-shadow:0 2px 3px rgba(0,0,0,0.16);-webkit-text-size-adjust:none;box-sizing:border-box;"
    target="_blank">Reset Password</a>
    <br><br>
    <b>Notice:</b> Este enlace será válido durante 15 minutos.
    <br><br>
    Si no solicitaste resetear tu contraseña, por favor, haz caso omiso a este correo.
</p>