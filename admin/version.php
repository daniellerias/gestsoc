<?php
require_once '../config.php';
require_once '../templates/header.php';
?>
<div class="container">
    <div class="center full-vertical">
    <img src="<?= $base_url ?>assets/img/SocGestLogo.png" alt="Logotipo GestSoc" style="max-width:120px;max-height:120px;margin-bottom:18px;">
    <p><strong><?= defined('APP_NAME') ? APP_NAME : '' ?> - <?= defined('APP_FULL_NAME') ? APP_FULL_NAME : '' ?></strong></p>
    <p>Versão: <strong><?= defined('VERSION') ? VERSION : '' ?></strong></p>
    <p>Última atualização: <strong><?= defined('LAST_UPDATE') ? LAST_UPDATE : '' ?></strong></p>
    <br>
    <p>Desenvolvido por <strong><a href="https://daniellerias.sectid.pt" target="_blank">Daniel Lérias</a></strong></p>
    <br>
    
        <a href="mailto:geral@sectid.pt?subject=Assistência GestSoc"><button type="submit" class="section_button" style="margin-top:10px;">
            <i class="fa-solid fa-envelope"></i> Contactar Programador
        </button></a>
</div>    
</div>
<?php require_once '../templates/footer.php'; ?>
