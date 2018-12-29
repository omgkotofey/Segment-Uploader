<?php

namespace Classes;

/**
 *  Класс, реализующий объект - файл c mac-адресами
 */

class MacFile
{
	private $name;
 	private $mime_type;
	private $content;
	private $mac_array;

	/**
	 * Контруктор класса
	 *
	 * @param String $file_name путь к файлу
	 * @param String $mime_type MIME-тип файла (если не передан - определеяется автоматически)
	 * @throws Exception
	 **/
	public function __construct($file_name, $mime_type = null, $content = null)
	{
		// Проверяем, если $content=null, значит в переменной $name - путь к файлу
		if(is_null($content))
		{
			// Получаем информацию по файлу (путь, имя и расширение файла)
			$path_info = pathinfo($file_name);
			// проверяем содержится ли в строке имя файла и можно ли прочитать файл
			if(file_exists($file_name) && !empty($path_info['basename']) && is_readable($file_name))
			{
				// Определяем имя/путь файл
				$this->name = $path_info['basename'];
				// Определяем MIME тип файла
				$this->mime_type = mime_content_type($file_name);
				// Загружаем файл
				$content = file_get_contents($file_name);
				// Если файл не пуст
				if($content!==false){
					// Сохраняем содержимое файла
					$this->content = $content;
					$this->getMacArrayFromContent();
				}
				else{
					throw new Exception('Не удалось прочесть содержимое файла "'.$file_name.'". Возможно он пуст.');
				}
			}
			else
			{
				throw new Exception('Невозможно прочесть файл "'.$file_name.'". Возможно он поврежден, защищиен от чтения или не существует.');
			}
		}
		else
		{
			// сохраняем имя файла
			$this->name = $name;
			// Если не был передан тип MIME пытаемся сами его определить
			if(is_null($mime)){
				$mime = mime_content_type($name);
			}
			// Сохраняем тип MIME файла
			$this->mime = $mime;
			// Сохраняем в свойстве класса содержимое файла
			$this->content = $content;
			$this->getMacArrayFromContent();
		};
	}

	/**
	 *  Метод вырезает все mac-адреса из содержимого файла в и сохраняет их в массиве $mac_array;
	 *
	 * @throws Exception
	 **/
	private function getMacArrayFromContent() { 
		if(!empty($this->content)){
			// Сохраняем все найденные в файле адреса
			preg_match_all('/([a-fA-F0-9]{2}[:|\-]?){6}/', $this->content, $mac_array);
			$this->mac_array = array_unique($mac_array[0]);
			//если адресов не найдено - кидаем исключение
			if (count($this->mac_array) == 0){
				throw new Exception('В файле "'.$file_name.'" не найдено на одного MAC-адреса.');
			}
		}
	}

	/**
	 *  Метод возвращает mac-адрес без разделителей
	 * 
	 * @param String $mac
	 * @return String 
	 **/
	private function sanitizeMac($mac) { 
		return str_replace(':', "", $mac);
	}

	/**
	 *  Метод возвращает имя файла
	 *
	 * @return String
	 **/
	public function getName() { 
		return $this->name;
	}

	/**
	 *  Метод возвращает имя файла
	 *
	 * @return String
	 **/
	public function getMime() { 
		return $this->mime_type;
	}

	/**
	 *  Метод возвращает содержимое файла
	 *
	 * @return String
	 **/
	public function getContent() { 
		return $this->content;
	}

	/**
	 *  Метод возвращает содержимое файла в виде массива mac-адресов
	 *
	 * @return Array 
	 **/
	public function getMacArray() { 
		return $this->mac_array;
	}

	/**
	 *  Метод возвращает количество mac-адресов в содержимом
	 *
	 * @return Array 
	 **/
	public function getMacCount() { 
		return count($this->mac_array);
	}
	
	/**
	 *  Метод возвращает все mac-адреса, найденные в файле
	 *
	 * @return String
	 **/
	public function getMacContent() { 
		return implode(PHP_EOL, array_map(array('MacFile', 'sanitizeMac'), $this->mac_array));
	}

	/**
	 *  Метод создает файл с со списком mac-адресов, указанных без разделителей. Физический файл-донор уничтожается.
	 *
	 * @param String $file_name имя создаваемого файла
	 **/
	public function createFormattedMacFile($file_name) { 
		if(file_put_contents($file_name, $this->getMacContent())){
			unlink($this->name);
			$this->name =  $file_name;
			return $this->name;
		}
		else{
			throw new Exception('Невозможно записать файл "'.$file_name.'.');
		}
	}
};
?>