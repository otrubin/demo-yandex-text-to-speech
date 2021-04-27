<?php

class SpeechKit
{
  const URL = 'https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize';

  // Идентификатор каталога
  const FOLDER_ID = "b1gdcgqcec11ufvvu010";


  private $iamToken;  // IAM-токен
  private $fileName;  // имя файла для сохранения речи
  private $lang;      // язык текста

  private $ch;
  private $response;

  private $error = "";

  public function __construct($iamToken)
  {
    $this->iamToken = $iamToken;
  }

  /**
   * Формируем строку ошибки
   */
  private function makeErrorInfo()
  {
    if (curl_errno($this->ch)) {
      $this->error = "Error: " . curl_error($this->ch);
    }
    if( $this->response )
    {
      $decodedResponse = json_decode($this->response, true);
      $this->error = "Error code: " . $decodedResponse["error_code"]
        . "; Error message: " . $decodedResponse["error_message"];
    }
  }

  /**
   * Получает:
   * $text - текст для распознавания в переменной
   * $lang - язык текста
   * $fileName - имя файла для сохранения речи
   * Делает запрос для синтеза речи из полученного текста
   * В случае удачи пишет в речь в файл и возвращает TRUE
   * В случае неудачи формирует строку ошибки и возвращает FALSE
   * Для получения строки с описание ошибки, следует вызвать метод getErrorInfo()
   */
  public function getSpeech($text, $lang, $fileName)
  {
    $this->lang = $lang;
    $this->fileName = $fileName;

    $post = "text=" . urlencode($text)
      . "&lang=" . $this->lang
      . "&folderId=" . CONFIG['folderId'];
    $headers = ['Authorization: Bearer ' . $this->iamToken];

    $this->ch = curl_init();

    curl_setopt($this->ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->ch, CURLOPT_URL, self::URL);
    curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($this->ch, CURLOPT_HEADER, false);
    if ($post !== false) {
      curl_setopt($this->ch, CURLOPT_POST, 1);
      curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

    $this->response = curl_exec($this->ch);

    $result = curl_getinfo($this->ch, CURLINFO_HTTP_CODE) === 200;
    // если все прошло успешно, пишем речь в файл
    if ($result)
    {
      file_put_contents($this->fileName, $this->response);
    } else {
      // при неудаче формируем строку ошибки
      $this->makeErrorInfo();
    }
    curl_close($this->ch);
    return $result;
  }

  /**
   * Возвращает ошибку
   */
  public function getErrorInfo()
  {
    return $this->error;
  }
}
