<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Ajustes</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Ajustes
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="pd-20 card-box">
    <div class="tab">
        <ul class="nav nav-tabs customtab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#general_settings" role="tab" aria-selected="true">Ajustes Generales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#logo_favicon" role="tab" aria-selected="false">Logo y Favicon</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#social_media" role="tab" aria-selected="false">Redes Sociales</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="general_settings" role="tabpanel">
                <div class="pd-20">
                    <form action="<?= route_to('update-general-settings') ?>" method="post" id="general_settings_form">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Título del Blog</label>
                                    <input type="text" class="form-control" name="blog_title" placeholder="Escribe el título del blog"><!-- Verificar por qué sale el error en get_settings() -->
                                    <span class="text-danger error-text blog_title_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog email</label>
                                    <input type="text" class="form-control" name="blog_email" placeholder="Escribe el email del blog">
                                    <span class="text-danger error-text blog_email_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Teléfono del Blog</label>
                                    <input type="text" class="form-control" name="blog_phone" placeholder="Escribe el teléfono del blog">
                                    <span class="text-danger error-text blog_phone_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Blog meta keywords</label>
                                    <input type="text" class="form-control" name="blog_meta_keywords" placeholder="Escribe las etiquetas del blog">
                                    <span class="text-danger error-text blog_meta_keywords_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Descripción del blog</label>
                            <textarea name="blog_meta-description" id="" cols="4" rows="3" class="form-control" placeholder="Escribe la descripción del blog"></textarea>
                            <span class="text-danger error-text blog_meta_description_error"></span>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="logo_favicon" role="tabpanel">
                <div class="pd-20">
                    ------ Logo y Favicon ------
                </div>
            </div>
            <div class="tab-pane fade" id="social_media" role="tabpanel">
                <div class="pd-20">
                    ------ Redes Sociales ------
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
    <script>
        $('#general_settings_form').on('submit',function(e){
            e.preventDefault();
            //CSRF HASH
            var csrfName = $('.ci_csrf_data').attr('name');
            var csrfHash = $('.ci_csrf_data').val();
            var form = this;
            var formdata = new FormData(form);
                formdata.append(csrfName, csrfHash);

            $.ajax({
                url:$(form).attr('action'),
                method:$(form).attr('method'),
                data:formdata,
                processData:false,
                dataType:'json',
                contentType:false,
                cache:false,
                beforeSend:function(){
                    toastr.remove();
                    $(form).find('span.error-text').text('');
                },
                success:function(response){
                    //Actualizar el CSRF Hash
                    $('.ci_csrf_data').val(response.token);

                    if ($.isEmptyObject(response.error)) {
                        if (response.status == 1) {
                            toastr.success(response.msg);
                        }else{
                            toastr.error(response.msg);
                        }
                    }else{
                        $.each(response.error, function(prefix, val){
                            $(form).find('span.'+prefix+'_error').text(val);
                        });
                    }
                }
            });
        });
    </script>
<?= $this->endSection() ?>