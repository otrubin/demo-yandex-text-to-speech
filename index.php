<?php
  require_once './speechkit.php';
  require_once './helpers.php';
  require_once './YandexIamToken.php';
  require_once './config.php';
?>;

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    <title>Яндекс SpeechKit</title>
  </head>
  <body>

    <div class="container">
    <h3 class="text-center">Преобразовать текст в речь</h3>
      <form method="post">
      <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="form-group">
              <label for="speechLang">Выберите язык:</label>
              <select class="form-control form-control-sm" name="speechLang" id="speechLang">
                <option value="ru-RU">Русский</option>
                <option value="en-US">Английский</option>
                <option value="tr-TR">Турецкий</option>
              </select>
            </div>
        </div>
        <div class="col-md-4">

        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group">
              <label for="textToSpeach">Введите текст:</label>
              <textarea class="form-control" name="textToSpeach" id="textToSpeach" rows="5"></textarea>
            </div>
            <button type="submit" name="btnTextToSpeech" class="btn btn-primary">Преобразовать!</button>


          <?php
            if (filter_input(INPUT_POST, 'btnTextToSpeech') !== null) // если нажата кнопка
            {
              $errorMessage = "";
              $successMessage = "";
              $text = filter_input(INPUT_POST, 'textToSpeach'); // получаем текст для перевода в речь
              if (!trim($text))
              {
                $errorMessage = makeErrorMessage("Введите текст!");
              }

              if (!$errorMessage) {
                // по QAuth-токену получаем действующий IAM-токен
                $iamToken = YandexIamToken::getToken(CONFIG['QAuth']);
                if(!$iamToken)
                {
                  $errorMessage = makeErrorMessage(YandexIamToken::getErrorInfo());
                }
              }

              if (!$errorMessage) {
                $lang = filter_input(INPUT_POST, 'speechLang'); // язык
                $fileName = date('Y-m-d-H-i-s') . '.ogg'; // имя файла
                // в конструктор передаем IAM-токен язык и имя файла с относительным путем
                $speechKit = new SpeechKit($iamToken);
                //получаем речь из текста
                if($speechKit->getSpeech($text, $lang, 'speeches/' . $fileName))
                {
                  $successMessage = makeSuccessMessage("Текст успешно преобразован в речь и записан в файл '$fileName'");
                } else {
                  $errorMessage = makeErrorMessage($speechKit->getErrorInfo());
                }
              }
            }
          ?>
          <div class="my-3">
            <?php
              if($errorMessage)
              {
                echo $errorMessage;
              }
              if($successMessage)
              {
                echo $successMessage;
              }
            ?>
          </div>
          <div class="my-3">
          <table class="table">
            <tbody>
              <?php
                $files = getSpeechFiles('speeches');
                foreach ($files as $file) {
                  // если это только что полученный файл, подсветим строку
                  $tr = $file === $fileName ? '<tr class="table-warning">' : '<tr>';
                  $tr .= "<td>$file</td>";
                  $tr .= "<td><audio src='speeches/$file' controls></audio></td>";
                  $tr .= '</tr>';
                  echo $tr;
                }
              ?>
              <tr>
                <td>Mark</td>
                <td>Otto</td>
              </tr>
            </tbody>
          </table>
          </div>

        </div>
      </div>
      </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
  </body>
</html>