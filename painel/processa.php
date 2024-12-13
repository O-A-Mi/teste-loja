<?php
    header('Content-Type: application/json');

    function reduzirImagem($caminhoOrigem, $caminhoDestino, $tamanhoMaximoKB = 100, $qualidadeInicial = 90){
        if (!file_exists($caminhoOrigem)){
            throw new Exception('Arquivo de imagem não encontrado.');
        }
    
        list($largura, $altura, $tipo) = getimagesize($caminhoOrigem);
    
        switch ($tipo){
            case IMAGETYPE_JPEG:
                $imagem = imagecreatefromjpeg($caminhoOrigem);
                break;
            case IMAGETYPE_PNG:
                $imagem = imagecreatefrompng($caminhoOrigem);
                break;
            default:
                throw new Exception('Formato de imagem não suportado.');
        }
    
        $qualidade = $qualidadeInicial;
        do{
            ob_start();
            imagejpeg($imagem, null, $qualidade);
            $conteudoImagem = ob_get_clean();
    
            $tamanhoAtualKB = strlen($conteudoImagem) / 1024;
            $qualidade -= 5;
        } while ($tamanhoAtualKB > $tamanhoMaximoKB && $qualidade > 10);
    
        file_put_contents($caminhoDestino, $conteudoImagem);
        imagedestroy($imagem);
    
        return [
            'caminhoDestino' => $caminhoDestino,
            'tamanhoFinalKB' => round($tamanhoAtualKB, 2),
            'qualidadeFinal' => $qualidade + 5
        ];
    }

    function urlAmigavel($texto){
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = preg_replace('/[áàâãäå]/u', 'a', $texto);
        $texto = preg_replace('/[éèêë]/u', 'e', $texto);
        $texto = preg_replace('/[íìîï]/u', 'i', $texto);
        $texto = preg_replace('/[óòôõö]/u', 'o', $texto);
        $texto = preg_replace('/[úùûü]/u', 'u', $texto);
        $texto = preg_replace('/[ç]/u', 'c', $texto);
        $texto = preg_replace('/[ñ]/u', 'n', $texto);
        $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
        $texto = preg_replace('/[\s-]+/', '-', $texto);
        $texto = trim($texto, '-');
        return $texto;
    }

    $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    try{
        $fantasia   = isset($_POST['fantasia']) ? $_POST['fantasia'] : null;
        $razao      = isset($_POST['razao']) ? $_POST['razao'] : null;
        $cnpj       = isset($_POST['cnpj']) ? $_POST['cnpj'] : null;
        $endereco   = isset($_POST['endereco']) ? $_POST['endereco'] : null;
        $cidade     = isset($_POST['cidade']) ? $_POST['cidade'] : null;
        $bairro     = isset($_POST['bairro']) ? $_POST['bairro'] : null;
        $cep        = isset($_POST['cep']) ? $_POST['cep'] : null;
        $estado     = isset($_POST['estado']) ? $_POST['estado'] : null;
        $telefone1  = isset($_POST['telefone1']) ? $_POST['telefone1'] : null;
        $telefone2  = isset($_POST['telefone2']) ? $_POST['telefone2'] : null;
        $telefone3  = isset($_POST['telefone3']) ? $_POST['telefone3'] : null;
        $email      = isset($_POST['email']) ? $_POST['email'] : null;

        $response['data'] = compact(
            'fantasia',
            'razao',
            'cnpj',
            'endereco',
            'cidade',
            'bairro',
            'cep',
            'estado',
            'telefone1',
            'telefone2',
            'telefone3',
            'email'
        );

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK){
            $diretorioDestino = 'assets/img/fornecedores/';
            if (!is_dir($diretorioDestino)){
                mkdir($diretorioDestino, 0755, true);
            }

            $arquivoTmp = $_FILES['logo']['tmp_name'];
            $nomeArquivo = urlAmigavel($fantasia) . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $caminhoDestino = $diretorioDestino . $nomeArquivo;

            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $extensaoArquivo = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

            if (!in_array($extensaoArquivo, $extensoesPermitidas)){
                throw new Exception('Formato de arquivo não permitido. Apenas JPG, PNG e GIF são aceitos.');
            }

            try{
                $resultadoReducao = reduzirImagem($arquivoTmp, $caminhoDestino);
                $response['data']['logo'] = $resultadoReducao['caminhoDestino'];
                $response['data']['tamanhoFinalKB'] = $resultadoReducao['tamanhoFinalKB'];
                $response['data']['qualidadeFinal'] = $resultadoReducao['qualidadeFinal'];
            } catch (Exception $e){
                die(json_encode(['success' => false, 'message' => 'Erro ao processar a imagem: ' . $e->getMessage()]));
            }
        }

        $response['success'] = true;
        $response['message'] = 'Cadastro realizado com sucesso!';
    } catch (Exception $e){
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
