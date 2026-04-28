<?php
require_once '../config.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<p style='color:red;'>❌ ID da associação inválido.</p>");
}

// Verificar se existem associações criadas
try {
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM associacoes");
    $totalAssociacoes = $stmtCount->fetchColumn();
    if ($totalAssociacoes == 0) {
        header('Location: criar_associacao.php');
        exit;
    }
} catch (PDOException $e) {
    die("<p style='color:red;'>❌ Erro ao verificar associações: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$associacao_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

// Buscar dados da associação
try {
    $stmt = $pdo->prepare("SELECT * FROM associacoes WHERE id = :id");
    $stmt->execute(['id' => $associacao_id]);
    $associacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$associacao) {
        die("<p style='color:red;'>❌ Associação não encontrada.</p>");
    }
} catch (PDOException $e) {
    die("<p style='color:red;'>❌ Erro ao buscar associação: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Caminho do config.php
$config_path = __DIR__ . '/../config.php';

// Teste de existência e permissões do config.php (mensagem visível na página)
$mensagem_config = '';
if (!file_exists($config_path)) {
    $mensagem_config = "<p style='color:red;'>❌ config.php não foi encontrado em $config_path</p>";
} elseif (!is_writable($config_path)) {
    $mensagem_config = "<p style='color:red;'>❌ config.php existe mas não tem permissões de escrita.</p>";
} else {
    $mensagem_config = "<p style='color:green;'>✅ config.php encontrado com permissões de escrita.</p>";
}

// Processar submissão do formulário principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_associacao'])) {
    // Sanitizar e validar dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_SCALAR);
    $morada = filter_input(INPUT_POST, 'morada', FILTER_SANITIZE_SPECIAL_CHARS);
    $contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_SPECIAL_CHARS);
    $nif = filter_input(INPUT_POST, 'nif', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (empty($nome)) {
        $mensagem = "<p style='color:red;'>❌ O nome da associação é obrigatório.</p>";
    } elseif ($email === false) {
        $mensagem = "<p style='color:red;'>❌ O email inserido é inválido.</p>";
    } else {
        // Processar upload do logotipo
        if (isset($_FILES['logotipo']) && $_FILES['logotipo']['error'] === UPLOAD_ERR_OK) {
            $nome_temporario = $_FILES['logotipo']['tmp_name'];
            // manter o nome original do ficheiro em vez de renomear
            $nome_arquivo = basename($_FILES['logotipo']['name']);
            $caminho_destino = IMG_DIR . $nome_arquivo;

            if (!is_dir(IMG_DIR)) {
                mkdir(IMG_DIR, 0777, true);
            }

            $allowed_types = ['image/png', 'image/jpeg', 'image/gif'];
            if (!in_array($_FILES['logotipo']['type'], $allowed_types)) {
                $mensagem = "<p style='color:red;'>❌ Tipo de arquivo inválido. Apenas PNG, JPEG e GIF são permitidos.</p>";
            } elseif (move_uploaded_file($nome_temporario, $caminho_destino)) {
                if ($associacao['logotipo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $associacao['logotipo'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $associacao['logotipo']);
                }
                $logotipo = 'assets/img/' . $nome_arquivo;
            } else {
                $mensagem = "<p style='color:red;'>❌ Erro ao fazer upload do logotipo.</p>";
            }
        } else {
            $logotipo = $associacao['logotipo'];
        }

        if (empty($mensagem)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE associacoes
                    SET nome = :nome, morada = :morada, contacto = :contacto, nif = :nif, email = :email, logotipo = :logotipo
                    WHERE id = :id
                ");

                $stmt->execute([
                    'nome' => $nome,
                    'morada' => $morada,
                    'contacto' => $contacto,
                    'nif' => $nif,
                    'email' => $email,
                    'logotipo' => $logotipo,
                    'id' => $associacao_id,
                ]);

                $mensagem = "<p style='color:green;'>✅ Associação atualizada com sucesso!</p>";

                $stmt = $pdo->prepare("SELECT * FROM associacoes WHERE id = :id");
                $stmt->execute(['id' => $associacao_id]);
                $associacao = $stmt->fetch(PDO::FETCH_ASSOC);

                // Removido processamento de usar_diabetico deste formulário


            } catch (PDOException $e) {
                $mensagem = "<p style='color:red;'>❌ Erro ao atualizar associação: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}

// Processar submissão do formulário de anos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_anos'])) {
    $anos_str = filter_input(INPUT_POST, 'anos', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($anos_str) {
        $anos_array = array_map('intval', array_map('trim', explode(',', $anos_str)));
        sort($anos_array);
        $anos_php_code = '$anos_disponiveis = [' . implode(', ', $anos_array) . '];';
        $config_content = file_get_contents($config_path);
        $new_config_content = preg_replace(
            '/^\$anos_disponiveis\s*=\s*\[.*?\];/m',
            $anos_php_code,
            $config_content
        );
        if (file_put_contents($config_path, $new_config_content)) {
            $mensagem_anos = "<p style='color:green;'>✅ Anos disponíveis atualizados com sucesso!</p>";
            $anos_disponiveis = $anos_array;
        } else {
            $mensagem_anos = "<p style='color:red;'>❌ Erro ao atualizar o ficheiro de configuração. Verifique as permissões de escrita.</p>";
        }
    } else {
        $mensagem_anos = "<p style='color:red;'>❌ A lista de anos não pode estar vazia.</p>";
    }
}

// Processar submissão do formulário de campos adicionais
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_campos_adicionais'])) {
    $usar_diabetico_post = isset($_POST['usar_diabetico']) ? 'true' : 'false';
    $config_content = file_get_contents($config_path);
    if ($config_content !== false) {
        $new_line = "\$usar_diabetico = $usar_diabetico_post;";
        if (preg_match('/\$usar_diabetico\s*=\s*(true|false)\s*;/', $config_content)) {
            $config_content = preg_replace('/\$usar_diabetico\s*=\s*(true|false)\s*;/', $new_line, $config_content, 1);
        } else {
            $config_content = preg_replace('/(\$show_associacao_on_edit\s*=\s*.*?;)/', "$1\n\n" . $new_line, $config_content, 1);
        }
        if (!is_writable($config_path)) {
            $mensagem_campos = "<p style='color:red;'>❌ O ficheiro config.php não tem permissões de escrita.</p>";
        } else {
            if (file_put_contents($config_path, $config_content) === false) {
                $mensagem_campos = "<p style='color:red;'>❌ Erro ao gravar no ficheiro config.php.</p>";
            } else {
                $config_reloaded = file_get_contents($config_path);
                if (preg_match('/\$usar_diabetico\s*=\s*(true|false)\s*;/', $config_reloaded, $m)) {
                    $usar_diabetico = ($m[1] === 'true');
                }
                $mensagem_campos = "<p style='color:green;'>✅ Campos adicionais atualizados com sucesso!</p>";
            }
        }
    }
}
// Processar submissão do formulário de métodos de pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_metodos'])) {
    $opcoes_metodos = ['Numerário', 'MB', 'MBWay', 'T. Bancária'];
    $metodos_array = [];
    foreach ($opcoes_metodos as $opcao) {
        $key = strtolower(str_replace([' ', '.', 'á'], ['_', '', 'a'], $opcao));
        if (isset($_POST['metodo_' . $key])) {
            $metodos_array[] = $opcao;
        }
    }
    $metodos_php_code = '$metodos = [' . implode(', ', array_map(function($m){ return "'" . addslashes($m) . "'"; }, $metodos_array)) . '];';
    $config_content = file_get_contents($config_path);
    $new_config_content = preg_replace(
        '/^\$metodos\s*=\s*\[.*?\];/m',
        $metodos_php_code,
        $config_content
    );
    if (file_put_contents($config_path, $new_config_content)) {
        $mensagem_metodos = "<p style='color:green;'>✅ Métodos de pagamento atualizados com sucesso!</p>";
        $metodos = $metodos_array;
    } else {
        $mensagem_metodos = "<p style='color:red;'>❌ Erro ao atualizar o ficheiro de configuração. Verifique as permissões de escrita.</p>";
    }
}
// include header only after all potential header() redirects and before any HTML output
require_once '../templates/header.php';
?>
<div class="container">

    <div style="text-align:right;font-size:0.95em;opacity:0.8;min-height:1.5em;padding:10px;">
        <?= $mensagem_config ?>
    </div>

    <h1>Configurar Associação</h1>

    <?php
        if (isset($mensagem)) echo $mensagem;
    ?>

    <!-- Formulário principal -->
    <h2>Dados da Associação</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-grid" style="display:flex;gap:18px;align-items:flex-start;">
            <div style="flex:1;">
                <div class="full-width">
                    <label for="nome">Nome da Associação:</label>
                    <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($associacao['nome']) ?>" required style="width:100%;max-width:600px;">
                </div>

                <div>
                    <label for="nif">NIF:</label>
                    <input type="text" name="nif" id="nif" inputmode="numeric" pattern="\d{9}" maxlength="9" value="<?= htmlspecialchars($associacao['nif'] ?? '') ?>" title="NIF com 9 dígitos">
                </div>

                <div>
                    <label for="contacto">Tel:</label>
                    <input type="text" name="contacto" id="contacto" value="<?= htmlspecialchars($associacao['contacto'] ?? '') ?>">
                </div>

                <div>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($associacao['email'] ?? '') ?>" style="width:100%;max-width:600px;">
                </div>

                <div class="full-width">
                    <label for="morada">Morada:</label>
                    <input type="text" name="morada" id="morada" value="<?= htmlspecialchars($associacao['morada'] ?? '') ?>" style="width:100%;max-width:600px;">
                </div>

                <div class="full-width">
                    <label for="logotipo">Logotipo:</label>
                    <?php if ($associacao['logotipo']) : ?>
                        <img src="<?= $base_url . htmlspecialchars($associacao['logotipo']) ?>" alt="Logotipo" width="100"><br>
                        <label>Alterar Logotipo:</label>
                    <?php endif; ?>
                    <input type="file" name="logotipo" id="logotipo">
                </div>
                
                <button type="submit" name="submit_associacao" class="section_button">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>

            <!-- Coluna para campos adicionais (lado direito) -->
            <div style="width:320px;flex:0 0 320px;border-left:1px solid #e0e0e0;padding-left:12px;">
                <!-- Formulário dos anos disponíveis -->
                <h3>Anos disponíveis para Quotas</h3>
                <div class="full-width" style="margin-bottom:18px;max-width:100%;">
                    <input type="text" name="anos" id="anos" value="<?= implode(', ', $anos_disponiveis) ?>" placeholder="Ex: 2024, 2025, 2026" style="width:100%;max-width:100%;">
                    <?php if (isset($mensagem_anos)) echo $mensagem_anos; ?>
                    <small>Insira os anos separados por vírgulas.</small>
                    <button type="submit" name="submit_anos" class="section_button" style="margin-bottom:12px;margin-top:8px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Anos
                    </button>
                </div>

                <!-- Formulário dos métodos de pagamento -->
                <h3 style="margin-top:18px;">Métodos de Pagamento</h3>
                <div class="full-width" style="margin-bottom:18px;max-width:100%;">
                    <?php
                    $opcoes_metodos = ['Numerário', 'MB', 'MBWay', 'T. Bancária'];
                    foreach ($opcoes_metodos as $opcao) {
                        $key = strtolower(str_replace([' ', '.', 'á'], ['_', '', 'a'], $opcao));
                        $checked = (isset($metodos) && in_array($opcao, $metodos)) ? 'checked' : '';
                        echo '<label style="display:block;margin-bottom:6px;">';
                        echo '<input type="checkbox" name="metodo_' . $key . '" value="1" ' . $checked . '> ' . htmlspecialchars($opcao);
                        echo '</label>';
                    }
                    ?>
                    <?php if (isset($mensagem_metodos)) echo $mensagem_metodos; ?>
                    <small>Selecione os métodos de pagamento disponíveis.</small>
                    <button type="submit" name="submit_metodos" class="section_button" style="margin-bottom:12px;margin-top:8px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Métodos
                    </button>
                </div>

                <!-- Campos adicionais -->
                <h3 style="margin-top:18px;">Campos Adicionais</h3>
                <p>Escolha os campos adicionais para esta associação.</p>
                <h4>Sócios</h4>
                <label for="usar_diabetico">
                    <input type="checkbox" name="usar_diabetico" id="usar_diabetico" value="1" <?= isset($usar_diabetico) && $usar_diabetico ? 'checked' : '' ?> />
                    Diabético
                </label>
                <?php if (isset($mensagem_campos)) echo $mensagem_campos; ?>
                <button type="submit" name="submit_campos_adicionais" class="section_button" style="margin-top:12px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Campos
                </button>
            </div>
        </div>
    </form>

</div>
<div class="spacer"></div>
<?php
require_once '../templates/footer.php';
?>