<?php
require_once '../config.php';
require_once '../templates/header.php';

// Buscar utilizadores
$users = $pdo->query("SELECT id, username, nome, email FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// Mensagem de feedback
$msg = '';
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
}
?>
<div class="container">
    <h1>Gestão de Utilizadores</h1>
    <?php if ($msg): ?>
        <p style="color:green;"> <?= $msg ?> </p>
    <?php endif; ?>
    <a href="criar_utilizador.php" class="section_button" style="margin-bottom:1em;">
        <i class="fa-solid fa-user-plus"></i> Novo Utilizador
    </a>
    <table cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Utilizador</th>
                <th>Nome</th>
                <th>Email</th>
                <th class="col-actions">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <div class="actions_buttons">
                        <button><a href="editar_utilizador.php?id=<?= $u['id'] ?>" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a></button>
                        <form method="post" action="eliminar_utilizador.php" style="display:inline;" onsubmit="return confirm('Eliminar este utilizador?');">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="actions_button" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="spacer"></div>
</div>
<?php require_once '../templates/footer.php'; ?>
