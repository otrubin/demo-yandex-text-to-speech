<?php
class YandexIamToken
{

  const TOKEN_FILE_NAME = 'iam-token.json';
  const URL = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';

  private static $ch;
  private static $response;
  private static $error = "";

  /**
   * Формируем строку ошибки
   */
  private static function makeErrorInfo()
  {
    if (curl_errno(self::$ch)) {
      self::$error = "Error: " . curl_error(self::$ch);
    }
    if( self::$response )
    {
      $decodedResponse = json_decode(self::$response, true);
      self::$error = "Error code: " . $decodedResponse["error_code"] . "; Error message: " . $decodedResponse["error_message"];
    }
  }

  /**
   * Получает QAuth токен и получает от яндекса информацию о IAM-токене в json формате
   * { "iamToken": "<iam-token>", "expiresAt": "<время до которого токен валиден>" }
   * Если запрос удачен, пишен в файл полученный json и возвращает iam-токен
   * При неудачном запросе возвращает FALSE
   */
  private static function  getNewToken($qauthToken)
  {
    self::$ch = curl_init();

    curl_setopt(self::$ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(self::$ch, CURLOPT_URL, self::URL);
    curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt(self::$ch, CURLOPT_HEADER, false);
    curl_setopt(self::$ch, CURLOPT_POST, 1);
    curl_setopt(self::$ch, CURLOPT_POSTFIELDS, '{"yandexPassportOauthToken":"' . $qauthToken . '"}');

    self::$response = curl_exec(self::$ch);

    $result = false;
    // Если информация о iam-токене получена без ошибок, пишем её в файл и возвращаем токен
    if (curl_getinfo(self::$ch, CURLINFO_HTTP_CODE) === 200)
    {
      file_put_contents(self::TOKEN_FILE_NAME, self::$response);
      $result = json_decode(self::$response)->iamToken;
    } else {
      // при неудаче формируем строку ошибки
      self::makeErrorInfo();
    }
    curl_close(self::$ch);
    return $result;
  }

  /**
   * Получает дату до которой токен годен
   * Если токен еще годен возвращает TRUE
   * Если время действия токена истекло возвращает FALSE
   */
  private static function isTokenValid($expiresAt)
  {
    return time() + 60 < strtotime($expiresAt); //минуту накидываем для гарантии
  }


  /**
   * Получает QAuth токен и возвращает IAM-токен
   * либо false при неудаче
   * для получения инфомации об ошибке надо вызвать метод getErrorInfo
   */
  public static function getToken($qauthToken)
  {
    self::$error = "";

    $jsonTokenInfo = file_exists(self::TOKEN_FILE_NAME) ? file_get_contents(self::TOKEN_FILE_NAME) : false;

    //если удалось получить json из файла
    if ($jsonTokenInfo !== false) {
      $tokenInfo = json_decode($jsonTokenInfo);
      //если iam-токен НЕ просрочен
      if (self::isTokenValid($tokenInfo->expiresAt)) {
        return $tokenInfo->iamToken; // возвращаем iam-токен
      }
    }
    //получаем инфу о токене от яндекса
    return self::getNewToken($qauthToken);
  }

  /**
   * Возвращает ошибку
   */
  public static function getErrorInfo()
  {
    return self::$error;
  }
}
