<?php require_once 'config.php'; ?>
<div class="container">
<div class="center full-vertical">
    <div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start; gap: 2em;">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'associacao_atualizada'): ?>
        <div class="alert alert-success" style="color:green; font-weight:bold; margin:12px 0;">✅ Associação atualizada com sucesso.</div>
    <?php endif; ?>

        <!-- Título e Logotipo da Associação -->
        <div style="flex: 1 1 320px; min-width: 280px; max-width: 400px;">
            <?php
            $stmt = $pdo->prepare("SELECT id, nome, logotipo FROM associacoes WHERE id = :id");
            $stmt->execute(['id' => 1]);
            $associacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($associacao) {
                if ($associacao['logotipo']) {
                    echo "<img src='" . htmlspecialchars($base_url . $associacao['logotipo']) . "' alt='Logotipo da Associação' style='max-width: 200px;'>";
                } else {
                    echo "<p>Logotipo não disponível</p>";
                }
                echo "<h2>" . htmlspecialchars($associacao['nome']) . "</h2>";
            }
            ?>
        </div>
        <!-- Estatísticas Homepage-->
        <div style="flex: 1 1 320px; min-width: 280px; max-width: 500px;">
            <?php
            $total_socios = $pdo->query("SELECT COUNT(*) FROM socios")->fetchColumn();
            $total_ativa = $pdo->query("SELECT COUNT(*) FROM socios WHERE estado = 'Ativa'")->fetchColumn();
            $total_suspensa = $pdo->query("SELECT COUNT(*) FROM socios WHERE estado = 'Suspensa'")->fetchColumn();
            // verificar se hoje é aniversário de algum sócio
            $hoje_md = date('m-d');
            $stmtAniv = $pdo->prepare("SELECT COUNT(*) FROM socios WHERE DATE_FORMAT(data_nascimento, '%m-%d') = :md");
            $stmtAniv->execute(['md' => $hoje_md]);
            $countAniv = $stmtAniv->fetchColumn();
            ?>
            <h3>Estatísticas</h3>
            <table cellpadding="5" cellspacing="0" style="margin:0 auto; min-width: 250px;">
                <tr>
                    <th>Total de Sócios</th>
                    <td><?php if ($total_socios) { echo $total_socios; } else { echo "0"; } ?></td>
                </tr>
                <tr>
                    <th>Inscrições Ativas</th>
                    <td><?php if ($total_ativa) { echo $total_ativa; } else { echo "0"; } ?></td>
                </tr>
                <tr>
                    <th>Inscrições Suspensas</th>
                    <td><?php if ($total_suspensa) { echo $total_suspensa; } else { echo "0"; } ?></td>
                </tr>
            </table>
            <?php if ($countAniv > 0): ?>
                <div class="alert" style="margin-top:12px; text-align:center;">
                    Hoje é o aniversário de <?= $countAniv ?> sócio<?= $countAniv>1?'s':'' ?>. <strong><a href="<?= $base_url ?>socios/aniversarios.php">Ver lista</a></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>