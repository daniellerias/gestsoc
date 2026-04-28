<?php
require_once '../config.php';
require_once '../templates/header.php';

// Obter a data atual
$hoje = date('m-d');

try {
    // Preparar a query para buscar socios cujo aniversário é hoje
    // A função DATE_FORMAT extrai o mês e o dia da coluna data_nascimento
    $stmt = $pdo->prepare("
        SELECT id, nome_completo, data_nascimento, email, telefone, diabetico 
    FROM socios 
        WHERE DATE_FORMAT(data_nascimento, '%m-%d') = :hoje
        ORDER BY nome_completo
    ");
    
    $stmt->execute(['hoje' => $hoje]);
    $aniversariantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<p style='color:red;'>❌ Erro ao buscar aniversariantes: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<div class="container">
    <h1>Aniversariantes de Hoje (<?= date('d/m') ?>)</h1>

    <?php if (empty($aniversariantes)): ?>
        <p>Não há aniversariantes hoje.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Data de Nascimento</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <?php if (!empty($usar_diabetico)): ?><th>Diabético</th><?php endif; ?>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aniversariantes as $associado): ?>
                        <tr>
                            <td><?= htmlspecialchars($associado['nome_completo']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($associado['data_nascimento']))) ?></td>
                            <td><?= htmlspecialchars($associado['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($associado['telefone'] ?? '') ?></td>
                            <?php if (!empty($usar_diabetico)): ?><td><?= intval($associado['diabetico']) ? 'Sim' : 'Não' ?></td><?php endif; ?>
                            <td>
                                <a href="enviar_email_aniversariantes.php?id=<?= htmlspecialchars($associado['id']) ?>" class="button">
                                    <i class="fas fa-envelope"></i> Enviar Email
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <div class="spacer"></div>
</div>

<?php
require_once '../templates/footer.php';
?>
