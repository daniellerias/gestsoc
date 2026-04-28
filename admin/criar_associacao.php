<?php
require_once '../config.php';
require_once '../templates/header.php';

// Processar submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitizar e validar dados
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_SCALAR);
    $morada = filter_input(INPUT_POST, 'morada', FILTER_SANITIZE_SPECIAL_CHARS);
    $contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_SPECIAL_CHARS);
    $nif = filter_input(INPUT_POST, 'nif', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    // Validar campos obrigatórios
    if (empty($nome)) {
        $mensagem = "<p style='color:red;'>❌ O nome da associação é obrigatório.</p>";
    } else {
        // Processar upload do logotipo
        $logotipo = null;
        if (isset($_FILES['logotipo']) && $_FILES['logotipo']['error'] === UPLOAD_ERR_OK) {
            $nome_temporario = $_FILES['logotipo']['tmp_name'];
            // manter o nome original do ficheiro
            $nome_arquivo = basename($_FILES['logotipo']['name']);
            $caminho_destino = IMG_DIR . $nome_arquivo;

            // Verificar se a pasta de destino existe, senão criar
            if (!is_dir(IMG_DIR)) {
                mkdir(IMG_DIR, 0777, true);
            }

            if (move_uploaded_file($nome_temporario, $caminho_destino)) {
                // guardar caminho relativo na base de dados
                $logotipo = 'assets/img/' . $nome_arquivo;
            } else {
                $mensagem = "<p style='color:red;'>❌ Erro ao fazer upload do logotipo.</p>";
            }
        }

        // Inserir dados na base de dados
        if (empty($mensagem)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO associacoes (nome, morada, contacto, nif, email, logotipo, criado_em)
                    VALUES (:nome, :morada, :contacto, :nif, :email, :logotipo, NOW())
                ");

                $stmt->execute([
                    'nome' => $nome,
                    'morada' => $morada,
                    'contacto' => $contacto,
                    'nif' => $nif,
                    'email' => $email,
                    'logotipo' => $logotipo,
                ]);

                $mensagem = "<p style='color:green;'>✅ Associação criada com sucesso!</p>";
            } catch (PDOException $e) {
                $mensagem = "<p style='color:red;'>❌ Erro ao criar associação: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}
?>
    <div class="container">
    <h1>Criar Nova Associação</h1>

    <?php if (isset($mensagem)) echo $mensagem; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="full-width">
                <label for="nome">Nome da Associação:</label>
                <input type="text" name="nome" id="nome" required>
            </div>

            <div>
                <label for="nif">NIF:</label>
                <input type="text" name="nif" id="nif">
            </div>

            <div>
                <label for="contacto">Tel:</label>
                <input type="text" name="contacto" id="contacto">
            </div>

            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email">
            </div>

            <div class="full-width">
                <label for="morada">Morada:</label>
                <input type="text" name="morada" id="morada">
            </div>

            <div class="full-width">
                <label for="logotipo">Logotipo:</label>
                <input type="file" name="logotipo" id="logotipo">
            </div>
        </div>
        <br>
        <button type="submit">Criar Associação</button>
    </form>
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .form-grid .full-width {
            grid-column: 1 / -1;
        }

        .form-grid label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-grid input {
            width: 100%;
            padding: 0.5rem;
            box-sizing: border-box;
        }
    </style>
    <div class="spacer"></div>
    </div>
<?php
require_once '../templates/footer.php';
?>
