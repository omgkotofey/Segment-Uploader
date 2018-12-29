<?php 

require_once 'vendor/autoload.php';

use Classes\Config;
use Classes\MacFile;

// файл обязательно деолжен придти и быть текстовым
if (!empty($_FILES) && ($_FILES['mac-file']['type'] == 'text/plain')){

	// генерируем уникальное имя для загружаемого файла 
	$unique_upload_filename = implode(".", [ time().uniqid(rand()) , pathinfo(basename($_FILES["mac-file"]["name"]), PATHINFO_EXTENSION)]);

	// загружаем файл в соответствубщую папку
	$upload_file_path = Config::get('UPLOAD_DIR') . $unique_upload_filename;

	// возвращаем JSON
	if (move_uploaded_file($_FILES['mac-file']['tmp_name'], $upload_file_path)) {
		try {
			$uploaded_file = new MacFile($upload_file_path);
			$mac_found = $uploaded_file->getMacCount();
			echo json_encode(['result' => 'success', 'message' => 'Файл загружен успешно', 'filename' => $unique_upload_filename, 'mac_count' => $mac_found]);
		} catch (\Exception $e) {
			echo json_encode(['result' => 'error', 'message' => $e->getMessage()]);
		}
		
	} 
	else {
		echo json_encode(['result' => 'error', 'message' => 'Невозможно загрузить данный файл. Возможно он поврежден или некорректен']);
	}
}
else{
	echo json_encode(['result' => 'error', 'message' => 'К загрузке принимаются только .txt файлы']);
}

?>
