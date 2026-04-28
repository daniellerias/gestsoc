<?php require_once '../config.php'; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Sócio</title>
    <link rel="stylesheet" href="<?php echo $base_url ?>assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<?php
// Validar o ID recebido
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo '<p style="color:red;">ID de associado inválido.</p>';
    exit;
}

// Buscar dados do associado
$stmt = $pdo->prepare('SELECT a.*, q.nome AS nome_quota, q.valor AS valor_quota, ass.nome AS nome_associacao FROM socios a LEFT JOIN tipos_quotas q ON a.quota_id = q.id LEFT JOIN associacoes ass ON a.associacao_id = ass.id WHERE a.id = :id');
$stmt->execute(['id' => $id]);
$assoc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assoc) {
    echo '<p style="color:red;">Associado não encontrado.</p>';
    exit;
}
?>
<div class="container">
    <h2>Detalhes do Sócio</h2>
    <br>
    <div style="width:100%; text-align:right; margin-bottom:16px;">
    <a href="<?php echo $base_url ?>socios/gerar_pdf_socio.php?id=<?= $id ?>" target="_blank" style="background:#1976d2; color:#fff; border:none; border-radius:4px; padding:8px 18px; font-size:1em; cursor:pointer; text-decoration:none; display:inline-block;">
        <i class="fa-solid fa-file-pdf"></i> Imprimir
    </a>
</div>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
        <tr>
            <th>Nº Sócio</th><td><?= htmlspecialchars($assoc['numero_socio']) ?></td>
            <th>Data de Adminssão</th><td><?= htmlspecialchars($assoc['data_registo']) ?></td>
            <th>Estado da Inscrição</th><td><?= htmlspecialchars($assoc['estado']) ?></td>
        </tr>
        <tr>
            <th>Nome</th><td colspan="5"><?= htmlspecialchars($assoc['nome_completo']) ?></td>
        </tr>
        <tr>
            <th>Data de Nascimento</th><td><?= htmlspecialchars($assoc['data_nascimento']) ?></td>
            <th>BI/CC</th><td><?= htmlspecialchars($assoc['bi_cc']) ?></td>
            <th>NIF</th><td><?= htmlspecialchars($assoc['nif']) ?></td>
        </tr>
        <tr>
            <th>Morada</th><td colspan="5"><?= htmlspecialchars($assoc['morada']) ?></td>
        </tr>
        <tr>
            <th>Telefone</th><td><?= htmlspecialchars($assoc['telefone']) ?></td>
            <th>Telemóvel</th><td><?= htmlspecialchars($assoc['telemovel']) ?></td>
            <th>Email</th><td><?= htmlspecialchars($assoc['email']) ?></td>
        </tr>
        <tr>
            <?php if (!empty($usar_diabetico)): ?>
            <th>Diabético</th><td><?= intval($assoc['diabetico']) ? 'Sim' : 'Não' ?></td>
            <?php endif; ?>
            <th>Quota</th><td><?= htmlspecialchars($assoc['nome_quota']) ?></td>
            <th>Valor</th><td><?= number_format((float)$assoc['valor_quota'], 2, ',', '.') . ' €' ?></td>
        </tr>
        <tr>
            <th>Anotações</th><td colspan="5"><?= nl2br(htmlspecialchars($assoc['anotacoes'])) ?></td>
        </tr>
    </table>
    <br>
    <h3>Pagamentos deste Sócio</h3>
    <?php
    $stmtPag = $pdo->prepare('SELECT p.*, q.nome AS nome_quota FROM pagamentos p JOIN tipos_quotas q ON p.quota_id = q.id WHERE p.associado_id = ? ORDER BY p.data_pagamento DESC');
    $stmtPag->execute([$id]);
    $pagamentos = $stmtPag->fetchAll(PDO::FETCH_ASSOC);
    if ($pagamentos): ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Data</th>
                <th>Valor</th>
                <th>Método</th>
                <th>Quota</th>
                <th>Ano</th>
                <th>Meses Pagos</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pagamentos as $pag): ?>
            <tr>
                <td><?= htmlspecialchars($pag['data_pagamento']) ?></td>
                <td><?= number_format($pag['montante'], 2, ',', '.') ?> €</td>
                <td><?= htmlspecialchars($pag['metodo_pagamento']) ?></td>
                <td><?= htmlspecialchars($pag['nome_quota']) ?></td>
                <td><?= htmlspecialchars($pag['referente_ano']) ?></td>
                <td>
                    <?php
                    $mesesNomes = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
                    $mesesPagos = [];
                    if (!empty($pag['meses'])) {
                        foreach (explode(',', $pag['meses']) as $m) {
                            $m = (int)trim($m);
                            if (isset($mesesNomes[$m])) $mesesPagos[] = $mesesNomes[$m];
                        }
                    }
                    echo htmlspecialchars(implode(', ', $mesesPagos));
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color:#bfa100">Sem pagamentos registados.</p>
    <?php endif; ?>
</div>
</body>
</html>
