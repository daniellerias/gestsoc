<?php
require_once '../config.php';
require_once '../templates/header.php';

$quotas = $pdo->query("SELECT * FROM tipos_quotas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
<h1>Configuração de Quotas</h1>
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success" style="color:green; font-weight:bold; margin-bottom:1em;">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>
<a href="criar_nova_quota.php" class="section_button">
    <i class="fa-solid fa-plus"></i> Criar Quota
</a>
<h3>Lista de Quotas</h3>
<table cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>Nome</th>
            <th class="col-medium">Valor</th>
            <th class="col-medium">Última Alteração / Por</th>
            <th class="col-actions">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quotas as $q): ?>
            <tr>
                <td><?= htmlspecialchars($q['nome']) ?></td>
                <td><?= htmlspecialchars(number_format($q['valor'], 2, ',', '.')) ?> €</td>
                <td>
                    <?= htmlspecialchars($q['atualizado_em'] ?? 'n/a') ?>
                    / <?= htmlspecialchars($q['atualizado_por'] ?? 'n/a') ?>
                </td>
                <td><div class="actions_buttons">
                    <button><a href="editar_quota.php?id=<?= $q['id'] ?>" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a></button>
                    <button><a href="eliminar_quota.php?id=<?= $q['id'] ?>" title="Eliminar" onclick="return confirm('Eliminar esta quota?');"><i class="fa-solid fa-trash"></i></a></button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="spacer"></div>
</div>
<?php
require_once '../templates/footer.php';
?>
