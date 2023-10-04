<div class="left-side-bar">
    <div class="brand-logo">
        <a href="index.html">
            <img src="/backend/vendors/images/deskapp-logo.svg" alt="" class="dark-logo" />
            <img src="/backend/vendors/images/deskapp-logo-white.svg" alt="" class="light-logo" />
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">
                <li>
                    <a href="<?= route_to('admin.home') ?>" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-home"></span><span class="mtext">Home</span>
                    </a>
                </li>
                <li>
                    <a href="" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-list"></span><span class="mtext">Categorías</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-newspaper"></span><span class="mtext">Posts</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="">All Posts</a></li>
                        <li><a href="">Add New</a></li>
                    </ul>
                </li>
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                <li>
                    <div class="sidebar-small-cap">Configuración</div>
                </li>
                <li>
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon bi bi-file-pdf"></span><span class="mtext">Documentation</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="introduction.html">Introduction</a></li>
                        <li><a href="getting-started.html">Getting Started</a></li>
                        <li><a href="color-settings.html">Color Settings</a></li>
                        <li>
                            <a href="third-party-plugins.html">Third Party Plugins</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?= route_to('admin.profile'); ?>" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-user"></span>
                        <span class="mtext">Perfil
                    </a>
                </li>
                <li>
                    <a href="<?= route_to('settings') ?>" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-settings"></span>
                        <span class="mtext">Ajustes
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>