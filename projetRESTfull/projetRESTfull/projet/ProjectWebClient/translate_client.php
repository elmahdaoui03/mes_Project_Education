<?php
session_start();
$error = "" ;
$darija = "";
$english = isset($_GET["english"]) && !empty($_GET["english"]) ? trim($_GET["english"]) : "";

function translateText($text) {
    $url = 'http://localhost:8080/Translation/rest/translate/' . urlencode($text);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true); 

    $response = curl_exec($ch);

    // Vérification des erreurs de cURL
    if (curl_errno($ch)) {
        curl_close($ch);
        return ["error" => curl_error($ch)];
    }

    // Vérification du code de statut HTTP
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ["error" => "Request failed with status code " . $httpCode];
    }

    // Vérification si la clé 'translatedText' existe dans la réponse
    if (isset($response)) {
        return $response;
    }

    // Si la traduction n'a pas pu être trouvée
    return ["error" => "Translation not found", "response" => $jsonResponse];
}

if (!empty($english)) {
    if (!isset($_SESSION["traduit"])) {
        $_SESSION["traduit"] = [];
    }

    $isExiste = false;

    foreach ($_SESSION["traduit"] as $translation) {
        if ( strtoupper(trim($translation["english"])) === strtoupper(trim($english))) {
            $isExiste = true;
            $darija = $translation["darija"];
            $error = "";
            break;
        }
    }

    if(!$isExiste){
        $textTraduit = translateText($english);
        if(!isset($textTraduit["error"]) ){
            $_SESSION["traduit"][] = ["english" => $english, "darija" => $textTraduit];
            $error = "";
            $darija = $textTraduit;
        }else {
            $error = $textTraduit["error"];
        }
    }
}

$traduit = $_SESSION["traduit"] ?? [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translate</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if(!empty($error)){echo '<p><strong>Error: </strong>'.$error.'</p>';}  ?>
    <h1>English to Darija Translation</h1>
<form action="" method="GET">
    <div class="chat-form">
        <!-- Zone de saisie de l'anglais -->
        <div class="message-input">
            <label for="english">Type your message (English):</label>
            <textarea name="english" id="english" placeholder="Type here..." class="area"><?= htmlspecialchars($english) ?></textarea>
        </div>
        
        <div class="submit-button">
            <input type="submit" value="Translate">
        </div>
    </div>
</form>

<div class="chat-container">
    <?php foreach ($traduit as $t): ?>
        <!-- Affichage de la bulle en anglais -->
        <div class="chat-bubble english">
            <span><?= htmlspecialchars($t["english"]) ?></span>
        </div>
        
        <!-- Affichage de la bulle en darija -->
        <div class="chat-bubble darija">
            <span><?= htmlspecialchars($t["darija"]) ?></span>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
