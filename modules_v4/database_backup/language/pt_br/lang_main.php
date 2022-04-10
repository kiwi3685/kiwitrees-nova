<?php

$lang['L_NOFTPPOSSIBLE'] = 'Você não tem alçada para funções de FTP !';
$lang['L_INFO_LOCATION'] = 'Sua localização é ';
$lang['L_INFO_DATABASES'] = 'Os seguintes bancos de dados estão no seu servidor:';
$lang['L_INFO_NODB'] = 'O banco de dados não existe.';
$lang['L_INFO_DBDETAIL'] = 'informação detalhada do banco de dados ';
$lang['L_INFO_DBEMPTY'] = 'O banco de dados está vazio !';
$lang['L_INFO_RECORDS'] = 'Registros';
$lang['L_INFO_SIZE'] = 'tamanho';
$lang['L_INFO_LASTUPDATE'] = 'Última atualização';
$lang['L_INFO_SUM'] = 'total';
$lang['L_INFO_OPTIMIZED'] = 'otimizada';
$lang['L_OPTIMIZE_DATABASES'] = 'Otimizar tabelas';
$lang['L_CHECK_TABLES'] = 'Verificar tabelas';
$lang['L_CLEAR_DATABASE'] = 'Limpar banco de dados';
$lang['L_DELETE_DATABASE'] = 'Excluir banco de dados';
$lang['L_INFO_CLEARED'] = 'foi limpa';
$lang['L_INFO_DELETED'] = 'foi excluida';
$lang['L_INFO_EMPTYDB1'] = 'Deve o banco de dados';
$lang['L_INFO_EMPTYDB2'] = ' ser truncado? (Atenção: Todos os dados serão perdidos para sempre!)';
$lang['L_INFO_KILLDB'] = ' ser excluído? (Atenção: Todos os dados serão perdidos para sempre!)';
$lang['L_PROCESSKILL1'] = 'O script tenta abortar o processo ';
$lang['L_PROCESSKILL2'] = 'para abortar.';
$lang['L_PROCESSKILL3'] = 'O script tenta desde  ';
$lang['L_PROCESSKILL4'] = ' sec. abortar o processo ';
$lang['L_HTACC_CREATE'] = 'Criar proteção do diretório';
$lang['L_ENCRYPTION_TYPE'] = 'Tipo de encriptação';
$lang['L_HTACC_BCRYPT'] = 'bcrypt - (Apache 2.4+, all systems)';
$lang['L_HTACC_MD5'] = 'MD5(APR) - (all systems)';
$lang['L_HTACC_SHA1'] = 'SHA1 - (all systems)';
$lang['L_HTACC_CRYPT'] = 'CRYPT - 8 characters maximum (Linux)';
$lang['L_HTACC_NO_ENCRYPTION'] = 'PLAIN TEXT - unencrypted (Windows)';
$lang['L_HTACCESS8'] = 'Já existe uma proteção do diretório. Se você criar novas, as antigas serão sobrescritas !';
$lang['L_HTACC_NO_USERNAME'] = 'Você tem que digitar um nome!';
$lang['L_PASSWORDS_UNEQUAL'] = 'As senhas não são idênticas ou são nulas !';
$lang['L_HTACC_CONFIRM_CREATE'] = 'Deve a proteção do diretório ser gravada agora ?';
$lang['L_HTACC_CONFIRM_DELETE'] = 'Are you sure you want to remove directory protection?';
$lang['L_HTACC_CREATED'] = 'A proteção do diretório foi criada.';
$lang['L_HTACC_CONTENT'] = 'Conteúdo do arquivo';
$lang['L_HTACC_CREATE_ERROR'] = 'Houve um erro durante a criação da proteção do diretório !<br>Favor criar os 2 arquivos manualmente com o seguinte conteúdo';
$lang['L_HTACC_CHECK_ERROR'] = 'It could not be checked whether the program is protected!<br>The simulated external access could not be carried out.';
$lang['L_HTACC_NOT_NEEDED'] = 'The program is protected by higher-level authorizations; local directory protection is not required.';
$lang['L_HTACC_COMPLETE'] = 'The program is protected, the directory protection is complete.';
$lang['L_HTACC_INCOMPLETE'] = 'The program is not protected, the directory protection is incomplete!';
$lang['L_HTACC_PROPOSED'] = 'Urgentemente recomendado';
$lang['L_HTACC_EDIT'] = 'Editar o .htaccess';
$lang['L_HTACCESS18'] = 'Criar o .htaccess em ';
$lang['L_HTACCESS19'] = 'Recarregar ';
$lang['L_HTACCESS20'] = 'Executar script';
$lang['L_HTACCESS21'] = 'Adicionar handler';
$lang['L_HTACCESS22'] = 'Tornar executável';
$lang['L_HTACCESS23'] = 'Listar Diretórios';
$lang['L_HTACCESS24'] = 'Documento de Erro';
$lang['L_HTACCESS25'] = 'Ativar rewrite';
$lang['L_HTACCESS26'] = 'Negar / Permitir';
$lang['L_HTACCESS27'] = 'Redirecionar';
$lang['L_HTACCESS28'] = 'Error Log';
$lang['L_HTACCESS29'] = 'Mais exemplos e documentação';
$lang['L_HTACCESS30'] = 'Provedor';
$lang['L_HTACCESS31'] = 'General';
$lang['L_HTACCESS32'] = 'Atenção! As diretivas do .htaccess afetam o comportamento do navegador.<br>Com conteúdo incorreto, as páginas podem ficar inacessíveis.';
$lang['L_DISABLEDFUNCTIONS'] = 'Desativar Funções';
$lang['L_NOGZPOSSIBLE'] = 'Como Zlib não está instalado, você não poderá usar as funções do GZip!';
$lang['L_DELETE_HTACCESS'] = 'Remover proteção de diretório (apagar .htaccess)';
$lang['L_WRONG_RIGHTS'] = "O arquivo ou o diretório '%s' não tem permissão de escrita para mim.<br>
As permissões (chmod) não estão configuradas apropriadamente ou não há privilégios suficientes para este usuário.<br>
Por favor configure corretamente as permissões usando o programa de FTP.<br>
O arquivo ou diretório necessitam de configuração para %s.<br>";
$lang['L_CANT_CREATE_DIR'] = "Não foi possível criar o diretório '%s'. 
Por favor utilize seu programa de FTP.";
$lang['L_TABLE_TYPE'] = 'Type';
$lang['L_CHECK'] = 'check';
$lang['L_OS'] = 'Operating system';
$lang['L_MOD_VERSION'] = 'MyOOS [Dumper] - Version';
$lang['L_NEW_MOD_VERSION'] = 'New Version';
$lang['L_NEW_MOD_VERSION_INFO'] = 'There is a new version of MyOOS [Dumper] available.';
$lang['L_UPDATED_IMPORTANT'] = 'Important: Before updating, please backup your files.';
$lang['L_UPDATE'] = 'Update now';
$lang['L_MYSQL_VERSION'] = 'MySQL-Version';
$lang['L_PHP_VERSION'] = 'PHP-Version';
$lang['L_MAX_EXECUTION_TIME'] = 'Max execution time';
$lang['L_PHP_EXTENSIONS'] = 'PHP-Extensions';
$lang['L_MEMORY'] = 'Memory';
$lang['L_FILE_MISSING'] = 'não pude encontrar o arquivo';
$lang['L_INSTALLING_UPDATES'] = 'Installing Updates';
$lang['L_UPDATE_SUCCESSFUL'] = 'Update successful';
$lang['L_UPDATE_FAILED'] = 'Update failed';
$lang['L_UP_TO_DATE'] = 'Current Version is up to date';
